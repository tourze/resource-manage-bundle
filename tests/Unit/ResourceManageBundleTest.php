<?php

namespace Tourze\ResourceManageBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\ResourceManageBundle\ResourceManageBundle;

class ResourceManageBundleTest extends TestCase
{
    public function testBundleIsInstantiable(): void
    {
        $bundle = new ResourceManageBundle();
        $this->assertInstanceOf(ResourceManageBundle::class, $bundle);
    }
}