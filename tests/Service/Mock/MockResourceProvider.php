<?php

namespace Tourze\ResourceManageBundle\Tests\Service\Mock;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;
use Tourze\ResourceManageBundle\Service\ResourceProvider;

/**
 * 资源提供者模拟类，用于测试
 * @phpstan-ignore-next-line
 */
class MockResourceProvider implements ResourceProvider
{
    private string $code;
    private string $label;
    private array $resources = [];
    private array $sendHistory = [];

    public function __construct(string $code = 'mock', string $label = 'Mock Provider')
    {
        $this->code = $code;
        $this->label = $label;
        
        // 添加默认资源
        $this->addResource(new MockResourceIdentity('resource-1', 'Resource 1'));
        $this->addResource(new MockResourceIdentity('resource-2', 'Resource 2'));
    }

    /**
     * 添加资源到提供者
     */
    public function addResource(ResourceIdentity $resource): void
    {
        $this->resources[$resource->getResourceId()] = $resource;
    }

    /**
     * 获取发送历史记录
     *
     * @return array 发送历史记录
     */
    public function getSendHistory(): array
    {
        return $this->sendHistory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities(): ?iterable
    {
        return $this->resources;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdentity(string $identity): ?ResourceIdentity
    {
        return $this->resources[$identity] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function sendResource(UserInterface $user, ?ResourceIdentity $identity, string $amount, int|float|null $expireDay = null, ?\DateTimeInterface $expireTime = null): void
    {
        // 记录发送历史，用于测试验证
        $this->sendHistory[] = [
            'user' => $user,
            'identity' => $identity,
            'amount' => $amount,
            'expireDay' => $expireDay,
            'expireTime' => $expireTime,
        ];
    }
} 