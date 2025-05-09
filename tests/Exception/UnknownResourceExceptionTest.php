<?php

namespace Tourze\ResourceManageBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\ResourceManageBundle\Exception\UnknownResourceException;

/**
 * 未知资源异常测试类
 */
class UnknownResourceExceptionTest extends TestCase
{
    /**
     * 测试异常继承关系
     */
    public function testExceptionExtends(): void
    {
        $exception = new UnknownResourceException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = '测试异常消息';
        $exception = new UnknownResourceException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常码
     */
    public function testExceptionCode(): void
    {
        $code = 404;
        $exception = new UnknownResourceException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试异常嵌套
     */
    public function testExceptionNesting(): void
    {
        $previous = new \RuntimeException('前一个异常');
        $exception = new UnknownResourceException('当前异常', 0, $previous);
        
        $this->assertSame($previous, $exception->getPrevious());
    }
} 