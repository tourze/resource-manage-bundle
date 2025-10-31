<?php

declare(strict_types=1);

namespace Tourze\ResourceManageBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\ResourceManageBundle\ResourceManageBundle;

/**
 * @internal
 */
#[CoversClass(ResourceManageBundle::class)]
#[RunTestsInSeparateProcesses]
final class ResourceManageBundleTest extends AbstractBundleTestCase
{
}
