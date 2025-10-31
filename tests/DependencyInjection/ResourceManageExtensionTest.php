<?php

namespace Tourze\ResourceManageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\ResourceManageBundle\DependencyInjection\ResourceManageExtension;

/**
 * @internal
 */
#[CoversClass(ResourceManageExtension::class)]
final class ResourceManageExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private ContainerBuilder $container;

    private ResourceManageExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
        $this->extension = new ResourceManageExtension();
    }

    /**
     * 测试扩展加载
     */
    public function testLoad(): void
    {
        $initialDefinitionCount = count($this->container->getDefinitions());

        $this->extension->load([], $this->container);

        // 验证服务配置文件被正确加载，容器中应该有新的服务定义
        $this->assertGreaterThan($initialDefinitionCount, count($this->container->getDefinitions()));

        // 验证 ResourceManager 服务被正确注册
        $this->assertTrue($this->container->hasDefinition('Tourze\ResourceManageBundle\Service\ResourceManager'));
    }
}
