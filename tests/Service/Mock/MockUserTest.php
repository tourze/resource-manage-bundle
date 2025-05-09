<?php

namespace Tourze\ResourceManageBundle\Tests\Service\Mock;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 模拟用户类测试
 */
class MockUserTest extends TestCase
{
    /**
     * 创建一个模拟用户
     */
    public function testCreateMockUser(): void
    {
        $mockUser = $this->createMock(UserInterface::class);
        $this->assertInstanceOf(UserInterface::class, $mockUser);
    }
    
    /**
     * 测试不同用户对象
     */
    public function testDifferentUserObjects(): void
    {
        $mockUser1 = $this->createMock(UserInterface::class);
        $mockUser2 = $this->createMock(UserInterface::class);
        
        $this->assertNotSame($mockUser1, $mockUser2);
    }
    
    /**
     * 测试带配置的模拟用户
     */
    public function testConfiguredMockUser(): void
    {
        $mockUser = $this->createMock(UserInterface::class);
        $mockUser->method('getUserIdentifier')->willReturn('test-user');
        
        $this->assertEquals('test-user', $mockUser->getUserIdentifier());
    }
} 