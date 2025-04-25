# LYRASOFT Token Coins Package

![Image](https://github.com/user-attachments/assets/f2da7373-8a4c-4b68-8c2d-24b3974ecdfd)

<!-- TOC -->
* [LYRASOFT Token Coins Package](#lyrasoft-token-coins-package)
  * [Installation](#installation)
    * [Menu](#menu)
  * [Usages](#usages)
    * [Available Actions](#available-actions)
    * [Type](#type)
    * [Check Available](#check-available)
  * [Callbacks](#callbacks)
    * [Inline Callback](#inline-callback)
    * [Global Callback](#global-callback)
  * [UI](#ui)
<!-- TOC -->

## Installation

Install from composer

```shell
composer require lyrasoft/token-coin
```

Then copy files to project

```shell
php windwalker pkg:install lyrasoft/token-coin -t routes -t migrations
```

### Menu

Add this to `sidemenu.menu.php`

```php
// Token Coins
$menu->link('點數歷史')
    ->to($nav->to('token_coin_history_list'))
    ->icon('fa-light fa-ticket');
```

## Usages

You can pre-add a column to record the remaining token coins in your user table.

```php
    $mig->createTable(
        User::class,
        function (Schema $schema) {
            // ...
            $schema->decimal('token_coins')->length('20,4');
            
            // ...
            
            $schema->addIndex('token_coins');
            
            // ...
        }
    );

    // OR

    $mig->updateTable(
        User::class,
        function (Schema $schema) {
            $schema->decimal('token_coins')->length('20,4')->after('password');
            $schema->addIndex('token_coins');
        }
    );
```

Then add token coins by the following code, you may add this code in seeders. 

```php
use \Lyrasoft\TokenCoin\Enum\TokenCoinAction;

foreach ($userIds as $userId) {
    /** @var \Lyrasoft\TokenCoin\Service\TokenCoinService $tokenCoinService */
    $tokenCoinService->modifyAndAddHistory(
        type: 'main',
        targetId: $userId,
        action: TokenCoinAction::PLUS,
        value: 500,
        // You can modify TokenCoinHistory entity here
        beforeSave: function (TokenCoinHistory $history) use ($currentUser, $tokenCoinData) {
            $history->setNote($tokenCoinData['note']); // The note messgage
            $history->setAgentId($currentUser->getId()); // Who did this action
        },
        // Udpdate remaining values
        updateRemain: function (float $remain, mixed $targetId, string|\BackedEnum $type) use ($orm, $currentUser) {
            $orm->updateBatch(
                User::class,
                [
                    'token_coins' => $remain,
                ],
                ['id' => $targetId]
            );
        }
    );
}
```

### Available Actions

```php
/** @var \Lyrasoft\TokenCoin\Service\TokenCoinService $tokenCoinService */

// plus/reduce/use actions
$tokenCoinService->plus($type, 500, $targetId, ...);
$tokenCoinService->reduce($type, 500, $targetId, ...);
$tokenCoinService->use($type, 500, $targetId, ...);

$tokenCoinService->modifyAndAddHistory($type, $action, $value, $targetId, ...);

// Other utilities

// Is enough to use/reduce
$tokenCoinService->isEnoughToReduce($type, $targetId, 500); // (bool)

// Is the remain larger than 0
$tokenCoinService->hasBalance($type, $targetId); // (bool)

// Get remaining value
$tokenCoinService->getRemain($type, $targetId); // (float)

// Get laat history entity
$tokenCoinService->getLastHistory($type, $targetId, sortBy: 'id'); // ?TokenCoinHistory

// Sum the total usage of a month of given time.
$tokenCoinService->getUsageTotalByMonth($type, $targetId, current: 'now'); // (float)

// Sum the total usage between two dates
$tokenCoinService->getUsageTotalBetween($type, $targetId, $start, $end); // (float)
```

### Type

You can use string or custom enum as type:

```php
$tokenCoinService->modifyAndAddHistory('reward');
$tokenCoinService->modifyAndAddHistory(MyTokenCoinType::REWARD);
```

### Check Available

If you are not allowing the overdraw, you can check the available token coins before using it.

```php
/** @var \Lyrasoft\TokenCoin\Service\TokenCoinService $tokenCoinService */
$orm->transaction(
    function () use ($tokenCoinService, $orm, $userId) {
        $type = MyTokenCoinType::REWARD; 
        $useValue = 699;
    
        // Check if the user has enough token coins
        if ($tokenCoinService->isEnoughToReduce($type, $userId, $useValue)) {
            $tokenCoinService->use($type, $useValue, $userId);
        } else {
            // Not enough token coins
            throw new \RuntimeException('Not enough token coins.', 403);
        }
    }
);
```

If you didn't check first, it may be reduce to negative value

```php
$history = $tokenCoinService->use($type, $useValue, $userId);

// $history->getRemain() < 0
```

If you allows the overdraw, but must larger than 0, you can check the balance:

```php
/** @var \Lyrasoft\TokenCoin\Service\TokenCoinService $tokenCoinService */
$orm->transaction(
    function () use ($tokenCoinService, $orm, $userId) {
        $type = MyTokenCoinType::REWARD; 
        $useValue = 699;
    
        // Check if the user has enough token coins
        if ($tokenCoinService->hasBalance($type, $userId)) {
            $tokenCoinService->use($type, $useValue, $userId);
        }
    }
);
```

## Callbacks

There are 2 callbacks you can use, `beforeSave` is to modify the history entity before saving, 
and `updateRemain` is to update the remaining value:

### Inline Callback

Use the callbacks inline ehen you modify coins:

```php
$tokenCoinService->plus(
    ...
    beforeSave: function (TokenCoinHistory $history) {
        // ...
    },
    updateRemain: function (float $remain, mixed $targetId, string|\BackedEnum $type) {
        // ...
    }
);
```

The callbacks is execute by `Container` so you can resort the arguments' ordering, And you can also inject services in the callbacks:

```php
function (string|\BackedEnum $type, \Windwalker\ORM\ORM $orm, float $remain, mixed $targetId) {
    // ...
}
```

### Global Callback

You can set the default callbacks in the `config` file:

```php
<?php

declare(strict_types=1);

namespace App\Config;

use Lyrasoft\Luna\Entity\User;
use Lyrasoft\TokenCoin\TokenCoinPackage;
use Windwalker\ORM\ORM;

return [
    'token-coin' => [
        // ...
        
        'callbacks' => [
            'before_save' => [
                // 'type' => function () {}
            ],

            'update_remain' => [
                'main' => function (float $remain, mixed $targetId, string|\BackedEnum $type) {
                    // ...
                }
            ],
        ],
    ]
];
```

## UI

This package provides a simple component to manage the token coins, you can use the `x-token-coin-changes-modal` in your project.

Add this component in `user-list` view.

```html
// user-list.blade.php

    ...

    <x-token-coin-changes-modal></x-token-coin-changes-modal>
</form>
```

Then add button to user list `list-toolbar`:

```html
    <button type="button" class="btn btn-sm btn-dark"
        data-bs-toggle="modal"
        data-bs-target="#token-coin-changes-modal"
    >
        <i class="far fa-ticket"></i>
        點數調整
    </button>
```

And add this code to `Usercontroller`:

```php
    // ...

    public function batch(
        AppContext $app,
        #[Autowire] UserRepository $repository,
        GridController $controller,
        ORM $orm,
    ): mixed {
        // ...
    
        if ($app->input('task') === 'token_coin_changes') {
            $tokenCoinService = $app->retrieve(TokenCoinService::class);
            $currentUser = $app->retrieve(\CurrentUser::class);
            $tokenCoinData = $app->input('token_coin');

            $ids = (array) $app->input('id');

            foreach ($ids as $id) {
                $tokenCoinService->modifyAndAddHistory(
                    type: 'main',
                    targetId: (int) $id,
                    action: TokenCoinAction::wrap($tokenCoinData['action']),
                    value: (float) $tokenCoinData['value'],
                    beforeSave: function (TokenCoinHistory $history) use ($currentUser, $tokenCoinData) {
                        $history->setNote($tokenCoinData['note']);
                        $history->setAgentId($currentUser->getId());
                    },
                    // You can put this to config callbacks
                    updateRemain: function (mixed $remain, mixed $targetId) use ($orm) {
                        $orm->updateBatch(
                            User::class,
                            [
                                'token_coins' => $remain,
                            ],
                            ['id' => $targetId]
                        );
                    }
                );
            }

            $app->addMessage(
                sprintf(
                    '已更新 %s 人的點數',
                    count($ids)
                ),
                'success'
            );

            return $app->getNav()->back();
        }

        // ...
    }
```

Now you can easily manage the token coins in the user list page.

![Image](https://github.com/user-attachments/assets/107640b2-ebc5-4b98-ba34-49aca95902d0)
