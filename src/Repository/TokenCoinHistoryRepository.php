<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin\Repository;

use Lyrasoft\Luna\Entity\User;
use Lyrasoft\TokenCoin\Entity\TokenCoinHistory;
use Unicorn\Attributes\ConfigureAction;
use Unicorn\Attributes\Repository;
use Unicorn\Repository\Actions\BatchAction;
use Unicorn\Repository\Actions\ReorderAction;
use Unicorn\Repository\Actions\SaveAction;
use Unicorn\Repository\ListRepositoryInterface;
use Unicorn\Repository\ListRepositoryTrait;
use Unicorn\Repository\ManageRepositoryInterface;
use Unicorn\Repository\ManageRepositoryTrait;
use Unicorn\Selector\ListSelector;

#[Repository(entityClass: TokenCoinHistory::class)]
class TokenCoinHistoryRepository implements ManageRepositoryInterface, ListRepositoryInterface
{
    use ManageRepositoryTrait;
    use ListRepositoryTrait;

    public function getListSelector(string|\UnitEnum $type): ListSelector
    {
        $selector = $this->createSelector();

        $selector->from(TokenCoinHistory::class)
            ->leftJoin(
                User::class,
                'agent',
                'token_coin_history.agent_id',
                'agent.id'
            )
            ->where('token_coin_history.type', $type);

        return $selector;
    }

    #[ConfigureAction(SaveAction::class)]
    protected function configureSaveAction(SaveAction $action): void
    {
        //
    }

    #[ConfigureAction(ReorderAction::class)]
    protected function configureReorderAction(ReorderAction $action): void
    {
        //
    }

    #[ConfigureAction(BatchAction::class)]
    protected function configureBatchAction(BatchAction $action): void
    {
        //
    }
}
