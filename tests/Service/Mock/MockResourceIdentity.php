<?php

namespace Tourze\ResourceManageBundle\Tests\Service\Mock;

use Tourze\ResourceManageBundle\Model\ResourceIdentity;

/**
 * Mock 资源标识类，用于测试
 *
 * @internal
 */
final class MockResourceIdentity implements ResourceIdentity
{
    private string $resourceId;

    private string $resourceLabel;

    public function __construct(
        string $resourceId = 'mock-id',
        string $resourceLabel = 'Mock Resource',
    ) {
        $this->resourceId = $resourceId;
        $this->resourceLabel = $resourceLabel;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getResourceLabel(): string
    {
        return $this->resourceLabel;
    }
}
