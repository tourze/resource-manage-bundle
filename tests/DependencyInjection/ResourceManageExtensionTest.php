<?php

namespace Tourze\ResourceManageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\ResourceManageBundle\DependencyInjection\ResourceManageExtension;
use Tourze\ResourceManageBundle\Service\ResourceManager;

class ResourceManageExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private ResourceManageExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ResourceManageExtension();
    }

    /**
     * 测试扩展加载
     */
    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务是否被正确加载
        $this->assertTrue($this->container->hasDefinition(ResourceManager::class));
    }
}