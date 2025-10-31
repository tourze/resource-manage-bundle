<?php

namespace Tourze\ResourceManageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ResourceManageBundle extends Bundle
{
    /**
     * @return array<string, mixed>
     */
    public static function getBundleDependencies(): array
    {
        return ['all' => true];
    }
}
