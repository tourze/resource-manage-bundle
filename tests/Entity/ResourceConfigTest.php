<?php

namespace Tourze\ResourceManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * 资源配置实体测试类
 *
 * @internal
 */
#[CoversClass(ResourceConfig::class)]
final class ResourceConfigTest extends TestCase
{
    private ResourceConfig $resourceConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceConfig = new ResourceConfig();
    }

    /**
     * 测试所有的getter和setter方法
     */
    public function testGetterAndSetterMethods(): void
    {
        // 设置测试数据
        $type = 'test-type';
        $typeId = 'test-type-id';
        $amount = 5;
        $expireDay = 30.5;
        $expireTime = new \DateTime('+30 days');

        // 测试type属性
        $this->resourceConfig->setType($type);
        $this->assertEquals($type, $this->resourceConfig->getType());

        // 测试typeId属性
        $this->resourceConfig->setTypeId($typeId);
        $this->assertEquals($typeId, $this->resourceConfig->getTypeId());

        // 测试amount属性
        $this->resourceConfig->setAmount($amount);
        $this->assertEquals($amount, $this->resourceConfig->getAmount());

        // 测试expireDay属性
        $this->resourceConfig->setExpireDay($expireDay);
        $this->assertEquals($expireDay, $this->resourceConfig->getExpireDay());

        // 测试expireTime属性
        $this->resourceConfig->setExpireTime($expireTime);
        $this->assertSame($expireTime, $this->resourceConfig->getExpireTime());
    }

    /**
     * 测试默认值
     */
    public function testDefaultValues(): void
    {
        // 只有amount有默认值为1，其他默认为null
        $this->assertEquals(1, $this->resourceConfig->getAmount());
        $this->assertNull($this->resourceConfig->getTypeId());
        $this->assertNull($this->resourceConfig->getExpireDay());
        $this->assertNull($this->resourceConfig->getExpireTime());
    }

    /**
     * 测试setter方法的void返回类型
     */
    public function testSetterMethodsReturnVoid(): void
    {
        // 验证setter方法可以正常调用且不返回值（void）
        // 这些调用不应该抛出异常，且无法在返回值上进行断言
        $this->resourceConfig->setType('test-type');
        $this->resourceConfig->setTypeId('test-type-id');
        $this->resourceConfig->setAmount(5);
        $this->resourceConfig->setExpireDay(30.5);
        $this->resourceConfig->setExpireTime(new \DateTime());

        // 验证设置后的值是否正确
        $this->assertEquals('test-type', $this->resourceConfig->getType());
        $this->assertEquals('test-type-id', $this->resourceConfig->getTypeId());
        $this->assertEquals(5, $this->resourceConfig->getAmount());
        $this->assertEquals(30.5, $this->resourceConfig->getExpireDay());
        $this->assertInstanceOf(\DateTime::class, $this->resourceConfig->getExpireTime());
    }

    /**
     * 测试使用null值
     */
    public function testWithNullValues(): void
    {
        // 设置非null值
        $this->resourceConfig->setTypeId('test-type-id');
        $this->resourceConfig->setAmount(5);
        $this->resourceConfig->setExpireDay(30.5);
        $this->resourceConfig->setExpireTime(new \DateTime());

        // 重置为null
        $this->resourceConfig->setTypeId(null);
        $this->resourceConfig->setAmount(null);
        $this->resourceConfig->setExpireDay(null);
        $this->resourceConfig->setExpireTime(null);

        // 验证是否为null
        $this->assertNull($this->resourceConfig->getTypeId());
        $this->assertNull($this->resourceConfig->getAmount());
        $this->assertNull($this->resourceConfig->getExpireDay());
        $this->assertNull($this->resourceConfig->getExpireTime());
    }
}
