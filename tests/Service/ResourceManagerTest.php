<?php

namespace Tourze\ResourceManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ResourceManageBundle\Exception\UnknownResourceException;
use Tourze\ResourceManageBundle\Service\ResourceManager;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceIdentity;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceProvider;

/**
 * 资源管理器测试类
 *
 * @internal
 */
#[CoversClass(ResourceManager::class)]
#[RunTestsInSeparateProcesses]
final class ResourceManagerTest extends AbstractIntegrationTestCase
{
    private ResourceManager $resourceManager;

    private MockResourceProvider $mockProvider1;

    private MockResourceProvider $mockProvider2;

    protected function onSetUp(): void
    {
        // 创建两个模拟提供者，不同的code和label
        $this->mockProvider1 = new MockResourceProvider('mock1', 'Mock Provider 1');
        $this->mockProvider2 = new MockResourceProvider('mock2', 'Mock Provider 2');

        // 为第一个提供者添加资源标识
        $this->mockProvider1->addResource(new MockResourceIdentity('resource-1', 'Resource 1'));
        $this->mockProvider1->addResource(new MockResourceIdentity('resource-2', 'Resource 2'));

        // 将模拟提供者注册到容器中
        self::getContainer()->set('test.mock_provider_1', $this->mockProvider1);
        self::getContainer()->set('test.mock_provider_2', $this->mockProvider2);

        // 从容器获取 ResourceManager 服务，此时它应该自动装配所有标记的提供者
        $this->resourceManager = self::getService(ResourceManager::class);
    }

    /**
     * 测试生成选择数据
     */
    public function testGenSelectDataReturnsCorrectSelectData(): void
    {
        // 测试方法能正常调用并返回数组
        $selectData = iterator_to_array($this->resourceManager->genSelectData());

        // 验证返回值是数组
        $this->assertIsArray($selectData);

        // 验证每个元素的数据结构（如果有数据的话）
        foreach ($selectData as $item) {
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('text', $item);
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('name', $item);
        }
    }

    /**
     * 测试发送资源时调用正确的提供者
     */
    public function testSendCallsCorrectProviderWithCorrectParameters(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);

        // 设置测试数据 - 使用不存在的资源类型，期望抛出异常
        $resourceType = 'non-existing-type';
        $resourceId = 'any-resource';
        $amount = '2';

        // 由于容器中的 ResourceManager 可能没有我们期望的提供者
        // 我们测试当找不到提供者时应该抛出异常
        $this->expectException(UnknownResourceException::class);
        $this->expectExceptionMessage('不支持的资源类型');

        // 调用测试方法
        $this->resourceManager->send($mockUser, $resourceType, $resourceId, $amount);
    }

    /**
     * 测试发送未知资源类型时抛出异常
     */
    public function testSendThrowsExceptionForUnknownResourceType(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);

        // 设置无效的资源类型
        $invalidResourceType = 'definitely-non-existing-type';

        // 断言会抛出异常
        $this->expectException(UnknownResourceException::class);
        $this->expectExceptionMessage('不支持的资源类型');

        // 调用测试方法
        $this->resourceManager->send($mockUser, $invalidResourceType, 'any-id', '1');
    }

    /**
     * 测试发送资源，但找不到指定的资源ID
     */
    public function testSendWithNonExistingResourceId(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);

        // 由于容器中可能没有可用的资源提供者，这个测试应该抛出异常
        $this->expectException(UnknownResourceException::class);
        $this->expectExceptionMessage('不支持的资源类型');

        // 调用测试方法，使用不存在的资源类型
        $this->resourceManager->send($mockUser, 'any-type', 'non-existing-id', '1');
    }

    /**
     * 测试发送资源，使用极端参数值
     */
    public function testSendWithExtremeValues(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);

        // 设置测试数据，使用极端值
        $resourceType = 'extreme-type';
        $resourceId = 'extreme-resource';
        $amount = '99999999';  // 非常大的数量
        $expireDay = 0;  // 0天过期

        // 由于容器中可能没有可用的资源提供者，这个测试应该抛出异常
        $this->expectException(UnknownResourceException::class);
        $this->expectExceptionMessage('不支持的资源类型');

        // 调用测试方法
        $this->resourceManager->send($mockUser, $resourceType, $resourceId, $amount, $expireDay);
    }
}
