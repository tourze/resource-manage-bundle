<?php

namespace Tourze\ResourceManageBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class ResourceManageExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
