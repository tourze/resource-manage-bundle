<?php

namespace Tourze\ResourceManageBundle\Tests\Service\Mock;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;
use Tourze\ResourceManageBundle\Service\ResourceProvider;

/**
 * Mock 资源提供者类，用于测试
 *
 * @internal
 */
final class MockResourceProvider implements ResourceProvider
{
    private string $code;

    private string $label;

    /** @var array<string, ResourceIdentity> */
    private array $identities = [];

    /** @var array<int, array<string, mixed>> */
    private array $sendHistory = [];

    public function __construct(
        string $code = 'mock-provider',
        string $label = 'Mock Provider',
    ) {
        $this->code = $code;
        $this->label = $label;

        // 添加默认资源
        $this->identities['default-1'] = new MockResourceIdentity('default-1', 'Default Resource 1');
        $this->identities['default-2'] = new MockResourceIdentity('default-2', 'Default Resource 2');
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array<string, ResourceIdentity>
     */
    public function getIdentities(): array
    {
        return $this->identities;
    }

    public function findIdentity(string $identity): ?ResourceIdentity
    {
        return $this->identities[$identity] ?? null;
    }

    public function sendResource(UserInterface $user, ?ResourceIdentity $identity, string $amount, int|float|null $expireDay = null, ?\DateTimeInterface $expireTime = null): void
    {
        $this->sendHistory[] = [
            'user' => $user,
            'identity' => $identity,
            'amount' => $amount,
            'expireDay' => $expireDay,
            'expireTime' => $expireTime,
        ];
    }

    /**
     * 添加资源
     */
    public function addResource(ResourceIdentity $identity): void
    {
        $this->identities[$identity->getResourceId()] = $identity;
    }

    /**
     * 获取发送历史
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSendHistory(): array
    {
        return $this->sendHistory;
    }
}
