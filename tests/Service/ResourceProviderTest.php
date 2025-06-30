<?php

namespace Tourze\ResourceManageBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceIdentity;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceProvider;

/**
 * 资源提供者接口测试类
 */
class ResourceProviderTest extends TestCase
{
    private MockResourceProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new MockResourceProvider('test-provider', 'Test Provider');
        
        // 添加自定义资源
        $this->provider->addResource(new MockResourceIdentity('custom-id', 'Custom Resource'));
    }

    /**
     * 测试获取代码
     */
    public function testGetCode(): void
    {
        $this->assertEquals('test-provider', $this->provider->getCode());
    }

    /**
     * 测试获取标签
     */
    public function testGetLabel(): void
    {
        $this->assertEquals('Test Provider', $this->provider->getLabel());
    }

    /**
     * 测试获取资源标识列表
     */
    public function testGetIdentities(): void
    {
        $identities = $this->provider->getIdentities();
        
        $this->assertNotNull($identities);
        $this->assertCount(3, $identities); // 默认2个 + 自定义1个
        
        // 验证资源是否包含自定义ID
        $this->assertArrayHasKey('custom-id', $identities);
        
        // 验证资源是否为预期类型
        foreach ($identities as $identity) {
            $this->assertInstanceOf(MockResourceIdentity::class, $identity);
        }
    }

    /**
     * 测试查找资源标识
     */
    public function testFindIdentity(): void
    {
        // 查找存在的资源
        $identity = $this->provider->findIdentity('custom-id');
        
        $this->assertNotNull($identity);
        $this->assertInstanceOf(MockResourceIdentity::class, $identity);
        $this->assertEquals('custom-id', $identity->getResourceId());
        $this->assertEquals('Custom Resource', $identity->getResourceLabel());
    }
    
    /**
     * 测试查找不存在的资源标识
     */
    public function testFindIdentity_withNonExistingId(): void
    {
        $identity = $this->provider->findIdentity('non-existing-id');
        $this->assertNull($identity);
    }

    /**
     * 测试发送资源
     */
    public function testSendResource(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 获取资源
        $identity = $this->provider->findIdentity('custom-id');
        
        // 发送资源
        $this->provider->sendResource($mockUser, $identity, '2', 30, new \DateTime());
        
        // 获取发送历史
        $sendHistory = $this->provider->getSendHistory();
        
        // 验证发送历史
        $this->assertCount(1, $sendHistory);
        $this->assertSame($mockUser, $sendHistory[0]['user']);
        $this->assertSame($identity, $sendHistory[0]['identity']);
        $this->assertEquals('2', $sendHistory[0]['amount']);
    }
    
    /**
     * 测试发送资源使用null身份
     */
    public function testSendResource_withNullIdentity(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 发送资源，身份为null
        $this->provider->sendResource($mockUser, null, '1');
        
        // 获取发送历史
        $sendHistory = $this->provider->getSendHistory();
        
        // 验证发送历史
        $this->assertCount(1, $sendHistory);
        $this->assertNull($sendHistory[0]['identity']);
    }
    
    /**
     * 测试发送多次资源
     */
    public function testSendResource_multipleTimes(): void
    {
        // 创建模拟用户
        $mockUser = $this->createMock(UserInterface::class);
        
        // 获取资源
        $identity1 = $this->provider->findIdentity('resource-1');
        $identity2 = $this->provider->findIdentity('resource-2');
        
        // 多次发送资源
        $this->provider->sendResource($mockUser, $identity1, '1');
        $this->provider->sendResource($mockUser, $identity2, '2');
        $this->provider->sendResource($mockUser, null, '3');
        
        // 获取发送历史
        $sendHistory = $this->provider->getSendHistory();
        
        // 验证发送历史
        $this->assertCount(3, $sendHistory);
        $this->assertSame($identity1, $sendHistory[0]['identity']);
        $this->assertSame($identity2, $sendHistory[1]['identity']);
        $this->assertNull($sendHistory[2]['identity']);
    }
} 