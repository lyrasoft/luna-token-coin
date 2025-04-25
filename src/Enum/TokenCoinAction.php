<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin\Enum;

use Windwalker\Utilities\Attributes\Enum\Color;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumTranslatableInterface;
use Windwalker\Utilities\Enum\EnumTranslatableTrait;

enum TokenCoinAction: string implements EnumTranslatableInterface
{
    use EnumTranslatableTrait;

    #[Color('success')]
    #[Title('增加')]
    case PLUS = 'plus';

    #[Color('danger')]
    #[Title('減少')]
    case REDUCE = 'reduce';

    #[Color('primary')]
    #[Title('使用')]
    case USE = 'use';

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans('token.coin.action.' . $this->getKey());
    }

    public function handleNumber(float $num): float
    {
        if ($this === self::PLUS) {
            return $num;
        }

        return -$num;
    }
}
