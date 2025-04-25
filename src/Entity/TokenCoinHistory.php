<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin\Entity;

use Lyrasoft\TokenCoin\Enum\TokenCoinAction;
use Brick\Math\BigDecimal;
use Brick\Math\BigNumber;
use Lyrasoft\Luna\Attributes\Author;
use Windwalker\Core\DateTime\Chronos;
use Windwalker\Core\DateTime\ServerTimeCast;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\CreatedTime;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Metadata\EntityMetadata;

use function Windwalker\unwrap_enum;

#[Table('token_coin_histories', 'token_coin_history')]
#[\AllowDynamicProperties]
class TokenCoinHistory implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected mixed $id = null;

    #[Column('target_id')]
    protected mixed $targetId = '';

    #[Column('order_id')]
    protected mixed $orderId = '';

    #[Column('agent_id')]
    protected mixed $agentId = '';

    #[Column('type')]
    protected string $type = '';

    #[Column('action')]
    #[Cast(TokenCoinAction::class)]
    protected TokenCoinAction $action;

    #[Column('value')]
    protected float $value = 0.0;

    #[Column('remain')]
    protected float $remain = 0.0;

    #[Column('note')]
    protected string $note = '';

    #[Column('date')]
    #[CastNullable(ServerTimeCast::class)]
    protected ?Chronos $date = null;

    #[Column('created')]
    #[CastNullable(ServerTimeCast::class)]
    #[CreatedTime]
    protected ?Chronos $created = null;

    #[Column('created_by')]
    #[Author]
    protected int $createdBy = 0;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    protected array $params = [];

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTargetId(): mixed
    {
        return $this->targetId;
    }

    public function setTargetId(mixed $targetId): static
    {
        $this->targetId = $targetId;

        return $this;
    }

    public function getOrderId(): mixed
    {
        return $this->orderId;
    }

    public function setOrderId(mixed $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getAgentId(): mixed
    {
        return $this->agentId;
    }

    public function setAgentId(mixed $agentId): static
    {
        $this->agentId = $agentId;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string|\UnitEnum $type): static
    {
        $this->type = unwrap_enum($type);

        return $this;
    }

    public function getAction(): TokenCoinAction
    {
        return $this->action;
    }

    public function setAction(string|TokenCoinAction $action): static
    {
        $this->action = TokenCoinAction::wrap($action);

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float|BigNumber $value): static
    {
        $this->value = BigDecimal::of($value)->toFloat();

        return $this;
    }

    public function getRemain(): float
    {
        return $this->remain;
    }

    public function setRemain(float|BigNumber $remain): static
    {
        $this->remain = BigDecimal::of($remain)->toFloat();

        return $this;
    }

    public function getDate(): ?Chronos
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface|string|null $date): static
    {
        $this->date = Chronos::tryWrap($date);

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getCreated(): ?Chronos
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface|string|null $created): static
    {
        $this->created = Chronos::tryWrap($created);

        return $this;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
