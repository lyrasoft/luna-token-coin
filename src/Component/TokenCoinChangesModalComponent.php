<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin\Component;

use Closure;
use Windwalker\Core\Edge\Attribute\EdgeComponent;
use Windwalker\Edge\Component\AbstractComponent;
use Windwalker\Utilities\Attributes\Prop;

#[EdgeComponent('token-coin-changes-modal')]
class TokenCoinChangesModalComponent extends AbstractComponent
{
    #[Prop]
    public string $id = '';

    #[Prop]
    public string $title = '';

    public function render(): Closure|string
    {
        return 'components.token-coin-changes-modal';
    }
}
