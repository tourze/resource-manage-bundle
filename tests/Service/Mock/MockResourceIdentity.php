<?php

namespace Tourze\ResourceManageBundle\Tests\Service\Mock;

use Tourze\ResourceManageBundle\Model\ResourceIdentity;

/**
 * 资源标识模拟类，用于测试
 * @phpstan-ignore-next-line
 */
class MockResourceIdentity implements ResourceIdentity
{
    private string $id;
    private string $label;

    public function __construct(string $id = 'mock-id', string $label = 'Mock Resource')
    {
        $this->id = $id;
        $this->label = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceLabel(): string
    {
        return $this->label;
    }
} 