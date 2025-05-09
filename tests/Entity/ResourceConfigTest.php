<?php

namespace Tourze\ResourceManageBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * 资源配置实体测试类
 */
class ResourceConfigTest extends TestCase
{
    private ResourceConfig $resourceConfig;

    protected function setUp(): void
    {
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
     * 测试方法返回对象本身以支持链式调用
     */
    public function testMethodChainingSupport(): void
    {
        $result = $this->resourceConfig
            ->setType('test-type')
            ->setTypeId('test-type-id')
            ->setAmount(5)
            ->setExpireDay(30.5)
            ->setExpireTime(new \DateTime());

        $this->assertSame($this->resourceConfig, $result);
    }

    /**
     * 测试使用null值
     */
    public function testWithNullValues(): void
    {
        // 设置非null值
        $this->resourceConfig
            ->setTypeId('test-type-id')
            ->setAmount(5)
            ->setExpireDay(30.5)
            ->setExpireTime(new \DateTime());

        // 重置为null
        $this->resourceConfig
            ->setTypeId(null)
            ->setAmount(null)
            ->setExpireDay(null)
            ->setExpireTime(null);

        // 验证是否为null
        $this->assertNull($this->resourceConfig->getTypeId());
        $this->assertNull($this->resourceConfig->getAmount());
        $this->assertNull($this->resourceConfig->getExpireDay());
        $this->assertNull($this->resourceConfig->getExpireTime());
    }
}
