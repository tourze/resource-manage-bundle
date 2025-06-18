<?php

namespace Tourze\ResourceManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Embeddable]
class ResourceConfig
{
    use TimestampableAware;
    #[ORM\Column(type: Types::STRING, length: 60, options: ['comment' => '类型'])]
    private string $type;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '类型值ID'])]
    private ?string $typeId = null;

    #[ORM\Column(nullable: true, options: ['comment' => '单次派发数量', 'default' => 1])]
    private ?int $amount = 1;

    #[ORM\Column(nullable: true, options: ['comment' => '派发后有效天数'])]
    private ?float $expireDay = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '派发后到期时间'])]
    private ?\DateTimeInterface $expireTime = null;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeId(): ?string
    {
        return $this->typeId;
    }

    public function setTypeId(?string $typeId): self
    {
        $this->typeId = $typeId;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExpireDay(): ?float
    {
        return $this->expireDay;
    }

    public function setExpireDay(?float $expireDay): self
    {
        $this->expireDay = $expireDay;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeInterface
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): self
    {
        $this->expireTime = $expireTime;

        return $this;
    }}
