# 资源管理包

[![PHP 版本](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net/)
[![许可证](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![构建状态](https://img.shields.io/badge/build-passing-brightgreen.svg)](#)
[![代码覆盖率](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)](#)

[English](README.md) | [中文](README.zh-CN.md)

一个通用的资源分发和管理框架，支持多种资源类型的统一管理和分发。

## 安装

```bash
composer require tourze/resource-manage-bundle
```

## 系统概述

资源管理系统是一个通用的资源发放和管理框架，支持多种类型资源的统一管理和分发。系统采用接口定义和依赖注入的方式，实现了高度的可扩展性和灵活性。

## 核心组件

### 1. ResourceIdentity 接口

定义了资源的基本标识接口，包含：

- `getResourceId()`: 获取资源唯一标识
- `getResourceLabel()`: 获取资源显示名称

### 2. ResourceProvider 接口

资源提供者的统一接口，负责具体资源类型的管理和发放：

- `getCode()`: 资源类型的唯一标识
- `getLabel()`: 资源类型的显示名称
- `getIdentities()`: 获取该类型下所有可用的资源列表
- `findIdentity()`: 查找特定资源
- `sendResource()`: 发放资源给用户

### 3. ResourceManager 服务

资源管理器，负责：

- 统一管理所有资源提供者
- 提供资源选择数据
- 统一的资源发放入口

## 已实现的资源类型

### 1. 优惠券资源 (CouponResourceProvider)

- 类型标识：`coupon`
- 功能：管理和发放优惠券
- 特点：与优惠券系统集成，支持优惠券的查找和发放

### 2. 实物奖品资源 (SpuOfferResourceProvider)

- 类型标识：`material`
- 功能：管理和发放实物商品
- 特点：与商品系统集成，支持商品库存管理和订单创建

### 3. 文本资源 (TextResourceProvider)

- 类型标识：`text`
- 功能：用于发放文本类型的安慰奖
- 特点：无实际发放行为，用于安慰奖场景

### 4. 虚拟资源 (VirtualResourceProvider)

- 类型标识：`virtual`
- 功能：用于发放虚拟奖品
- 特点：无实际发放行为，用于虚拟奖品场景

## 如何实现新的资源类型

1. 创建资源实体类并实现 ResourceIdentity 接口

```php
class MyResource implements ResourceIdentity
{
    public function getResourceId(): string
    {
        return $this->id;
    }

    public function getResourceLabel(): string
    {
        return $this->name;
    }
}
```

2. 创建资源提供者类并实现 ResourceProvider 接口

```php
class MyResourceProvider implements ResourceProvider
{
    public function getCode(): string
    {
        return 'my_resource';
    }

    public function getLabel(): string
    {
        return '我的资源';
    }

    public function getIdentities(): iterable|null
    {
        // 返回所有可用资源
    }

    public function findIdentity(string $identity): ResourceIdentity|null
    {
        // 查找特定资源
    }

    public function sendResource(BizUser $user, ResourceIdentity $identity, string $amount, ?int $expireDay = null, ?\DateTimeInterface $expireTime = null): void
    {
        // 实现资源发放逻辑
    }
}
```

3. 资源提供者会自动注册到系统中（通过 AutoconfigureTag 注解）

## 使用示例

```php
// 注入资源管理器
private ResourceManager $resourceManager;

// 发放资源
$this->resourceManager->send(
    $user,          // 用户
    'coupon',       // 资源类型
    'COUPON001',    // 资源ID
    '1',            // 数量
    30,             // 过期天数（可选）
    null            // 过期时间（可选）
);

// 获取所有可用资源类型
$resources = $this->resourceManager->genSelectData();
```

## 注意事项

1. 资源类型的 Code 必须全局唯一
2. 实现新的资源类型时，需要同时实现资源标识和提供者
3. 资源发放时需要处理异常情况
4. 建议为新增的资源类型添加单元测试

## License

本项目遵循 MIT 许可证 - 详情请参阅 [LICENSE](LICENSE) 文件。
