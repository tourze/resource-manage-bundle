<?php

namespace Tourze\ResourceManageBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

/**
 * 通用的资源服务
 */
#[AutoconfigureTag(ResourceProvider::TAG_NAME)]
interface ResourceProvider
{
    final public const TAG_NAME = 'resource.provider';

    /**
     * 资源标志
     */
    public function getCode(): string;

    /**
     * 资源名
     */
    public function getLabel(): string;

    /**
     * 获取资源的标识列表
     *
     * @return iterable<ResourceIdentity>|null
     */
    public function getIdentities(): ?iterable;

    /**
     * 查找资源标志
     */
    public function findIdentity(string $identity): ?ResourceIdentity;

    /**
     * 发送资源（请求）
     */
    public function sendResource(UserInterface $user, ?ResourceIdentity $identity, string $amount, int|float|null $expireDay = null, ?\DateTimeInterface $expireTime = null): void;
}
