<?php

namespace Tourze\ResourceManageBundle\Model;

/**
 * 资源的定义
 */
interface ResourceIdentity
{
    /**
     * 获取资源标识
     */
    public function getResourceId(): string;

    /**
     * 获取资源标签
     */
    public function getResourceLabel(): string;
}
