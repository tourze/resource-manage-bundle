<?php

namespace Tourze\ResourceManageBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;
use Tourze\ResourceManageBundle\Tests\Service\Mock\MockResourceIdentity;

/**
 * 资源标识接口测试类
 */
class ResourceIdentityTest extends TestCase
{
    private MockResourceIdentity $mockIdentity;

    protected function setUp(): void
    {
        $this->mockIdentity = new MockResourceIdentity('test-id', 'Test Resource');
    }

    /**
     * 测试资源ID获取
     */
    public function testGetResourceId(): void
    {
        $this->assertEquals('test-id', $this->mockIdentity->getResourceId());
    }

    /**
     * 测试资源标签获取
     */
    public function testGetResourceLabel(): void
    {
        $this->assertEquals('Test Resource', $this->mockIdentity->getResourceLabel());
    }

    /**
     * 测试接口实现
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(ResourceIdentity::class, $this->mockIdentity);
    }

    /**
     * 测试使用不同参数构造
     */
    public function testConstructWithDifferentParameters(): void
    {
        $id = 'custom-id';
        $label = 'Custom Label';
        $identity = new MockResourceIdentity($id, $label);
        
        $this->assertEquals($id, $identity->getResourceId());
        $this->assertEquals($label, $identity->getResourceLabel());
    }

    /**
     * 测试使用默认参数构造
     */
    public function testConstructWithDefaultParameters(): void
    {
        $identity = new MockResourceIdentity();
        
        $this->assertEquals('mock-id', $identity->getResourceId());
        $this->assertEquals('Mock Resource', $identity->getResourceLabel());
    }
} 