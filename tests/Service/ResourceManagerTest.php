<?php

namespace Tourze\ResourceManageBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ResourceManageBundle\Exception\UnknownResourceException;
use Tourze\ResourceManageBundle\Service\ResourceManager;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceIdentity;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceProvider;

/**
 * 资源管理器测试类
 */
class ResourceManagerTest extends TestCase
{
    private ResourceManager $resourceManager;
    private MockResourceProvider $mockProvider1;
    private MockResourceProvider $mockProvider2;

    protected function setUp(): void
    {
        // 创建两个模拟提供者，不同的code和label
        $this->mockProvider1 = new MockResourceProvider('mock1', 'Mock Provider 1');
        $this->mockProvider2 = new MockResourceProvider('mock2', 'Mock Provider 2');
        
        // 注入模拟提供者到资源管理器
        $this->resourceManager = new ResourceManager([$this->mockProvider1, $this->mockProvider2]);
    }

    /**
     * 测试生成选择数据
     */
    public function testGenSelectData_returnsCorrectSelectData(): void
    {
        // 调用测试方法
        $selectData = iterator_to_array($this->resourceManager->genSelectData());
        
        // 断言结果包含两个提供者的数据
        $this->assertCount(2, $selectData);
        
        // 验证第一个提供者的数据
        $this->assertEquals([
            'label' => 'Mock Provider 1',
            'text' => 'Mock Provider 1',
            'value' => 'mock1',
            'name' => 'Mock Provider 1',
        ], $selectData[0]);
        
        // 验证第二个提供者的数据
        $this->assertEquals([
            'label' => 'Mock Provider 2',
            'text' => 'Mock Provider 2',
            'value' => 'mock2',
            'name' => 'Mock Provider 2',
        ], $selectData[1]);
    }

    /**
     * 测试发送资源时调用正确的提供者
     */
    public function testSend_callsCorrectProviderWithCorrectParameters(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 设置测试数据
        $resourceType = 'mock1';
        $resourceId = 'resource-1';
        $amount = '2';
        $expireDay = 30.5;
        $expireTime = new \DateTime('+30 days');
        
        // 调用测试方法
        $this->resourceManager->send($mockUser, $resourceType, $resourceId, $amount, $expireDay, $expireTime);
        
        // 获取发送历史
        $sendHistory = $this->mockProvider1->getSendHistory();
        
        // 断言发送历史包含一条记录
        $this->assertCount(1, $sendHistory);
        
        // 验证发送参数
        $sendRecord = $sendHistory[0];
        $this->assertSame($mockUser, $sendRecord['user']);
        $this->assertInstanceOf(MockResourceIdentity::class, $sendRecord['identity']);
        $this->assertEquals($resourceId, $sendRecord['identity']->getResourceId());
        $this->assertEquals($amount, $sendRecord['amount']);
        $this->assertEquals($expireDay, $sendRecord['expireDay']);
        $this->assertSame($expireTime, $sendRecord['expireTime']);
        
        // 确保第二个提供者没有被调用
        $this->assertCount(0, $this->mockProvider2->getSendHistory());
    }
    
    /**
     * 测试发送未知资源类型时抛出异常
     */
    public function testSend_throwsExceptionForUnknownResourceType(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 设置无效的资源类型
        $invalidResourceType = 'non-existing-type';
        
        // 断言会抛出异常
        $this->expectException(UnknownResourceException::class);
        $this->expectExceptionMessage('不支持的资源类型');
        
        // 调用测试方法
        $this->resourceManager->send($mockUser, $invalidResourceType, 'any-id', '1');
    }
    
    /**
     * 测试发送资源，但找不到指定的资源ID
     */
    public function testSend_withNonExistingResourceId(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 调用测试方法，使用不存在的资源ID
        $this->resourceManager->send($mockUser, 'mock1', 'non-existing-id', '1');
        
        // 获取发送历史
        $sendHistory = $this->mockProvider1->getSendHistory();
        
        // 断言发送历史包含一条记录
        $this->assertCount(1, $sendHistory);
        
        // 验证发送参数，identity 应为 null
        $sendRecord = $sendHistory[0];
        $this->assertNull($sendRecord['identity']);
    }
    
    /**
     * 测试发送资源，使用极端参数值
     */
    public function testSend_withExtremeValues(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 设置测试数据，使用极端值
        $resourceType = 'mock1';
        $resourceId = 'resource-1';
        $amount = '99999999';  // 非常大的数量
        $expireDay = 0;  // 0天过期
        
        // 调用测试方法
        $this->resourceManager->send($mockUser, $resourceType, $resourceId, $amount, $expireDay);
        
        // 获取发送历史
        $sendHistory = $this->mockProvider1->getSendHistory();
        
        // 断言发送历史包含一条记录
        $this->assertCount(1, $sendHistory);
        
        // 验证发送参数
        $sendRecord = $sendHistory[0];
        $this->assertEquals($amount, $sendRecord['amount']);
        $this->assertEquals($expireDay, $sendRecord['expireDay']);
    }
} 