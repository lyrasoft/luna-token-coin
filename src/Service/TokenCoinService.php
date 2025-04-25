<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin\Service;

use Brick\Math\BigDecimal;
use Lyrasoft\TokenCoin\Entity\TokenCoinHistory;
use Lyrasoft\TokenCoin\Enum\TokenCoinAction;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\DI\Attributes\Service;
use Windwalker\ORM\ORM;

use function Windwalker\chronos;
use function Windwalker\unwrap_enum;

#[Service]
class TokenCoinService
{
    public function __construct(
        protected ApplicationInterface $app,
        protected ChronosService $chronosService,
        protected ORM $orm,
    ) {
        //
    }

    public function plus(
        string|\UnitEnum $type,
        mixed $targetId,
        mixed $value,
        ?\Closure $beforeSave = null,
        ?\Closure $updateRemain = null,
    ): TokenCoinHistory {
        return $this->modifyAndAddHistory(
            $type,
            $targetId,
            TokenCoinAction::PLUS,
            $value,
            $beforeSave,
            $updateRemain
        );
    }

    public function reduce(
        string|\UnitEnum $type,
        mixed $targetId,
        mixed $value,
        ?\Closure $beforeSave = null,
        ?\Closure $updateRemain = null,
    ): TokenCoinHistory {
        return $this->modifyAndAddHistory(
            $type,
            $targetId,
            TokenCoinAction::REDUCE,
            $value,
            $beforeSave,
            $updateRemain
        );
    }

    public function use(
        string|\UnitEnum $type,
        mixed $targetId,
        mixed $value,
        ?\Closure $beforeSave = null,
        ?\Closure $updateRemain = null,
    ): TokenCoinHistory {
        return $this->modifyAndAddHistory(
            $type,
            $targetId,
            TokenCoinAction::USE,
            $value,
            $beforeSave,
            $updateRemain
        );
    }

    public function isEnoughToReduce(
        string|\UnitEnum $type,
        mixed $targetId,
        mixed $value,
    ): bool {
        $remain = $this->getRemain($type, $targetId);

        $value = BigDecimal::of($value);

        return $value->isGreaterThan($remain);
    }

    public function hasBalance(
        string|\UnitEnum $type,
        mixed $targetId,
    ): bool {
        $remain = $this->getRemain($type, $targetId);

        return BigDecimal::of($remain)->isGreaterThan(0);
    }

    public function getUsageTotalByMonth(
        string|\UnitEnum $type,
        mixed $targetId,
        \DateTimeInterface|string $current = 'now',
        \DateTimeZone|string $tz = 'UTC',
    ): float {
        $current = chronos($current);

        $date = $current->setTimezone($tz);
        $date = $date->modify('00:00:00');

        return $this->getUsageTotalByBetween(
            $type,
            $targetId,
            $date,
            $date->modify('last day of this month 23:59:59')
        );
    }

    public function getUsageTotalByBetween(
        string|\UnitEnum $type,
        mixed $targetId,
        \DateTimeInterface|string $start,
        \DateTimeInterface|string $end
    ): float {
        $start = chronos($start);
        $end = chronos($end);

        $query = $this->orm->select()
            ->selectRaw('SUM(value) AS value')
            ->from(TokenCoinHistory::class)
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->where('action', TokenCoinAction::USE)
            ->where('date', '>=', $start)
            ->where('date', '<=', $end);

        return (float) ($query->result() ?? 0);
    }

    public function modifyAndAddHistory(
        string|\UnitEnum $type,
        mixed $targetId,
        TokenCoinAction $action,
        mixed $value,
        ?\Closure $beforeSave = null,
        ?\Closure $updateRemain = null,
    ): TokenCoinHistory {
        $value = BigDecimal::of($value)->abs();

        return $this->orm->transaction(
            function () use ($updateRemain, $type, $targetId, $beforeSave, $value, $action) {
                /** @var TokenCoinHistory $last */
                $last = $this->getLastHistory($type, $targetId);

                if ($last) {
                    $lastRemain = BigDecimal::of($last->getRemain());

                    if ($action === TokenCoinAction::PLUS) {
                        $remain = $lastRemain->plus($value);
                    } else {
                        $remain = $lastRemain->minus($value);
                    }
                } else {
                    if ($action === TokenCoinAction::PLUS) {
                        $remain = $value;
                    } else {
                        $remain = $value->negated();
                    }
                }

                $history = new TokenCoinHistory();
                $history->setType($type);
                $history->setTargetId($targetId);
                $history->setAction($action);
                $history->setValue($value);
                $history->setRemain($remain);
                $history->setDate('now');

                $typeString = unwrap_enum($type);

                $beforeSave ??= $this->getBeforeSaveCallback($type);

                if ($beforeSave) {
                    $history = $this->app->call(
                        $beforeSave,
                        [
                            'history' => $history,
                            TokenCoinHistory::class => $history,
                        ]
                    ) ?? $history;
                }

                $history = $this->orm->createOne($history);

                $updateRemain ??= $this->getUpdateRemainCallback($type);

                if ($updateRemain) {
                    $this->updateRemain(
                        $type,
                        $targetId,
                        $updateRemain
                    );
                }

                return $history;
            }
        );
    }

    public function updateRemain(
        string|\UnitEnum $type,
        mixed $targetId,
        \Closure $handler,
    ): mixed {
        return $this->orm->transaction(
            function () use ($handler, $targetId, $type) {
                return $this->app->call(
                    $handler,
                    [
                        'type' => $type,
                        'targetId' => $targetId,
                        'remain' => $this->getRemain($type, $targetId),
                    ]
                );
            }
        );
    }

    public function getRemain(string|\UnitEnum $type, mixed $targetId): float
    {
        /** @var TokenCoinHistory $last */
        $last = $this->getLastHistory($type, $targetId);

        return (float) $last?->getRemain();
    }

    public function getLastHistory(
        string|\UnitEnum $type,
        mixed $targetId,
        string $sortBy = 'id'
    ): ?TokenCoinHistory {
        /** @var ?TokenCoinHistory $item */
        $item = $this->orm->from(TokenCoinHistory::class)
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->order($sortBy, 'DESC')
            ->forUpdate()
            ->get(TokenCoinHistory::class);

        return $item;
    }

    public static function numberFormat(mixed $num, int $decimals = 2): string
    {
        $num = (float) (string) $num;

        $num = number_format($num, $decimals);

        if (str_contains($num, '.')) {
            $num = rtrim($num, '0');
            $num = rtrim($num, '.');
        }

        return $num;
    }

    public function getBeforeSaveCallback(string|\BackedEnum $type): ?\Closure
    {
        $type = unwrap_enum($type);

        return $this->app->config('token-coin.callbacks.before_save.' . $type);
    }

    public function getUpdateRemainCallback(string|\BackedEnum $type): ?\Closure
    {
        $type = unwrap_enum($type);

        return $this->app->config('token-coin.callbacks.update_remain.' . $type);
    }
}
