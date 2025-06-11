# Resource Manage Bundle 设计文档

## 1. 系统概述

Resource Manage Bundle 是一个用于管理和分发各种数字资源权益的核心组件。在营销活动、用户激励、会员权益等场景中，系统需要灵活地配置、派发和管理各种类型的资源。

### 1.1 核心价值

- **统一抽象**：将不同类型的权益统一抽象为资源，提供一致的管理接口
- **灵活配置**：支持不同资源类型的个性化配置参数
- **灵活扩展**：支持新资源类型的快速接入，无需修改核心代码
- **可追溯**：完整的操作日志和审计功能，确保资源流转的透明性

### 1.2 应用场景

- **营销活动配置**：配置活动奖励，如优惠券、积分、抽奖次数等
- **用户激励**：签到奖励、任务完成奖励、等级提升奖励
- **会员权益**：VIP特权、专属折扣、免费服务等
- **补偿机制**：系统故障补偿、客服处理补偿等

### 1.3 典型配置场景

在配置营销活动时，管理员可以选择不同类型的资源奖励：

- **优惠券类型**：从优惠券列表中选择具体优惠券，配置发放数量
- **积分类型**：填写积分数量，设置积分过期天数
- **抽奖次数类型**：选择抽奖活动，填写次数和过期时间

每种资源类型都有其特定的配置参数和验证规则。

## 2. 系统架构

### 2.1 核心组件

```ascii
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Resource      │    │   Processor     │    │   Repository    │
│   Identity      │◄───┤   Manager       │◄───┤   Manager       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Resource      │    │   Processor     │    │   Audit         │
│   Configuration │    │   Registry      │    │   Logger        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 2.2 设计原则

- **单一职责**：每个组件专注于特定功能
- **开闭原则**：对扩展开放，对修改封闭
- **依赖倒置**：依赖抽象而非具体实现
- **接口隔离**：提供细粒度的接口定义

## 3. 核心模型

### 3.1 ResourceType（资源类型）

```php
namespace Tourze\ResourceManageBundle\Model;

/**
 * 资源类型值对象
 * 使用值对象模式，既保证类型安全又允许扩展
 */
readonly class ResourceType
{
    public function __construct(
        public string $value,
        public string $name,
        public ?string $description = null,
        public array $metadata = []
    ) {}

    /**
     * 创建资源类型
     */
    public static function create(string $value, string $name, ?string $description = null, array $metadata = []): self
    {
        return new self($value, $name, $description, $metadata);
    }

    /**
     * 从字符串创建资源类型（向后兼容）
     */
    public static function fromString(string $value): self
    {
        return new self($value, ucfirst($value));
    }

    /**
     * 比较两个资源类型是否相等
     */
    public function equals(ResourceType $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * 获取字符串表示
     */
    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### 3.2 ResourceConfiguration（资源配置）

```php
namespace Tourze\ResourceManageBundle\Model;

/**
 * 资源配置抽象基类
 */
abstract readonly class ResourceConfiguration
{
    public function __construct(
        public ResourceType $type,
        public int $quantity = 1,
        public ?int $expireDays = null
    ) {}

    /**
     * 转换为ResourceIdentity
     */
    abstract public function toResourceIdentity(): ResourceIdentity;

    /**
     * 获取配置参数（用于序列化等场景）
     */
    abstract public function getParameters(): array;

    /**
     * 获取配置结构定义
     */
    abstract public static function getConfigurationSchema(): array;
}
```

#### 3.2.1 优惠券配置

```php
namespace Tourze\ResourceManageBundle\Model\Configuration;

use Tourze\ResourceManageBundle\Model\ResourceConfiguration;
use Tourze\ResourceManageBundle\Model\ResourceType;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

readonly class CouponConfiguration extends ResourceConfiguration
{
    public function __construct(
        public string $couponId,
        public int $quantity = 1
    ) {
        parent::__construct(
            type: ResourceType::create('coupon', '优惠券'),
            quantity: $quantity
        );
    }

    public function toResourceIdentity(): ResourceIdentity
    {
        return new ResourceIdentity(
            id: uniqid('coupon_'),
            type: $this->type,
            name: '优惠券',
            metadata: ['coupon_id' => $this->couponId],
            quantity: $this->quantity
        );
    }

    public function getParameters(): array
    {
        return [
            'coupon_id' => $this->couponId,
            'quantity' => $this->quantity
        ];
    }

    public static function getConfigurationSchema(): array
    {
        return [
            'type' => 'coupon',
            'name' => '优惠券',
            'class' => self::class,
            'fields' => [
                'couponId' => [
                    'type' => 'select',
                    'label' => '选择优惠券',
                    'required' => true,
                    'source' => 'coupon_list'
                ],
                'quantity' => [
                    'type' => 'number',
                    'label' => '发放数量',
                    'default' => 1,
                    'min' => 1,
                    'required' => true
                ]
            ]
        ];
    }
}
```

#### 3.2.2 积分配置

```php
namespace Tourze\ResourceManageBundle\Model\Configuration;

use Tourze\ResourceManageBundle\Model\ResourceConfiguration;
use Tourze\ResourceManageBundle\Model\ResourceType;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

readonly class CreditConfiguration extends ResourceConfiguration
{
    public function __construct(
        public int $amount,
        public ?int $expireDays = null
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('积分数量必须大于0');
        }

        parent::__construct(
            type: ResourceType::create('credit', '积分'),
            quantity: 1,
            expireDays: $expireDays
        );
    }

    public function toResourceIdentity(): ResourceIdentity
    {
        $expireAt = $this->expireDays ? time() + ($this->expireDays * 24 * 60 * 60) : null;

        return new ResourceIdentity(
            id: uniqid('credit_'),
            type: $this->type,
            name: '积分',
            metadata: ['amount' => $this->amount],
            quantity: $this->quantity,
            expireAt: $expireAt
        );
    }

    public function getParameters(): array
    {
        return [
            'amount' => $this->amount,
            'expire_days' => $this->expireDays
        ];
    }

    public static function getConfigurationSchema(): array
    {
        return [
            'type' => 'credit',
            'name' => '积分',
            'class' => self::class,
            'fields' => [
                'amount' => [
                    'type' => 'number',
                    'label' => '积分数量',
                    'required' => true,
                    'min' => 1
                ],
                'expireDays' => [
                    'type' => 'number',
                    'label' => '过期天数',
                    'required' => false,
                    'min' => 1,
                    'help' => '不填写则永不过期'
                ]
            ]
        ];
    }
}
```

#### 3.2.3 抽奖次数配置

```php
namespace Tourze\ResourceManageBundle\Model\Configuration;

use Tourze\ResourceManageBundle\Model\ResourceConfiguration;
use Tourze\ResourceManageBundle\Model\ResourceType;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

readonly class LotteryConfiguration extends ResourceConfiguration
{
    public function __construct(
        public string $lotteryId,
        public int $times,
        public ?int $expireDays = null
    ) {
        if ($times <= 0) {
            throw new \InvalidArgumentException('抽奖次数必须大于0');
        }

        parent::__construct(
            type: ResourceType::create('lottery', '抽奖次数'),
            quantity: 1,
            expireDays: $expireDays
        );
    }

    public function toResourceIdentity(): ResourceIdentity
    {
        $expireAt = $this->expireDays ? time() + ($this->expireDays * 24 * 60 * 60) : null;

        return new ResourceIdentity(
            id: uniqid('lottery_'),
            type: $this->type,
            name: '抽奖次数',
            metadata: [
                'lottery_id' => $this->lotteryId,
                'times' => $this->times
            ],
            quantity: $this->quantity,
            expireAt: $expireAt
        );
    }

    public function getParameters(): array
    {
        return [
            'lottery_id' => $this->lotteryId,
            'times' => $this->times,
            'expire_days' => $this->expireDays
        ];
    }

    public static function getConfigurationSchema(): array
    {
        return [
            'type' => 'lottery',
            'name' => '抽奖次数',
            'class' => self::class,
            'fields' => [
                'lotteryId' => [
                    'type' => 'select',
                    'label' => '选择抽奖活动',
                    'required' => true,
                    'source' => 'lottery_list'
                ],
                'times' => [
                    'type' => 'number',
                    'label' => '抽奖次数',
                    'required' => true,
                    'min' => 1,
                    'default' => 1
                ],
                'expireDays' => [
                    'type' => 'number',
                    'label' => '过期天数',
                    'required' => false,
                    'min' => 1,
                    'help' => '不填写则永不过期'
                ]
            ]
        ];
    }
}
```

### 3.3 ResourceIdentity（资源标识）

```php
namespace Tourze\ResourceManageBundle\Model;

use Tourze\ResourceManageBundle\Enum\ResourceStatus;

readonly class ResourceIdentity
{
    public function __construct(
        public string $id,
        public ResourceType $type,
        public string $name,
        public array $metadata,
        public int $quantity,
        public ?int $expireAt = null,
        public ResourceStatus $status = ResourceStatus::ACTIVE
    ) {}

    /**
     * 便捷的创建方法
     */
    public static function create(
        string $typeValue,
        string $name,
        array $metadata = [],
        int $quantity = 1,
        ?int $expireAt = null
    ): self {
        return new self(
            id: uniqid($typeValue . '_'),
            type: ResourceType::fromString($typeValue),
            name: $name,
            metadata: $metadata,
            quantity: $quantity,
            expireAt: $expireAt
        );
    }

    /**
     * 从配置创建
     */
    public static function fromConfiguration(ResourceConfiguration $config): self
    {
        return $config->toResourceIdentity();
    }
}
```

### 3.4 ResourceProcessor（资源处理器）

```php
namespace Tourze\ResourceManageBundle\Processor;

use Symfony\Component\Security\Core\User\UserInterface;

interface ResourceProcessorInterface
{
    /**
     * 派发资源
     */
    public function grant(ResourceIdentity $resource, UserInterface $user, array $context = []): GrantResult;
    
    /**
     * 核销资源
     */
    public function consume(ResourceIdentity $resource, UserInterface $user, array $context = []): ConsumeResult;
    
    /**
     * 查询资源状态
     */
    public function query(ResourceIdentity $resource, UserInterface $user): QueryResult;
    
    /**
     * 支持的资源类型（返回字符串数组用于匹配）
     */
    public function getSupportedTypes(): array;
    
    /**
     * 检查是否支持指定的资源类型
     */
    public function supports(ResourceType $type): bool;
}
```

## 4. 核心接口

### 4.1 ResourceManager（资源管理器）

```php
namespace Tourze\ResourceManageBundle\Manager;

use Symfony\Component\Security\Core\User\UserInterface;

interface ResourceManagerInterface
{
    /**
     * 派发资源
     */
    public function grant(ResourceIdentity $resource, UserInterface $user, array $context = []): GrantResult;
    
    /**
     * 从配置派发资源
     */
    public function grantFromConfiguration(ResourceConfiguration $config, UserInterface $user, array $context = []): GrantResult;
    
    /**
     * 核销资源
     */
    public function consume(ResourceIdentity $resource, UserInterface $user, array $context = []): ConsumeResult;
    
    /**
     * 获取用户资源清单
     */
    public function getUserResources(UserInterface $user, ?ResourceType $type = null): array;
}
```

### 4.2 ResourceRepository（资源仓储）

```php
namespace Tourze\ResourceManageBundle\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

interface ResourceRepositoryInterface
{
    public function save(ResourceIdentity $resource): void;
    public function findById(string $id): ?ResourceIdentity;
    public function findByUser(UserInterface $user, ?ResourceType $type = null): array;
    public function updateStatus(string $id, ResourceStatus $status): void;
    public function batchSave(array $resources): void;
}
```

## 5. 处理器实现示例

### 5.1 优惠券处理器

```php
namespace Tourze\ResourceManageBundle\Processor;

use Tourze\CouponCoreBundle\Service\CouponServiceInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('resource_manage.processor')]
class CouponProcessor implements ResourceProcessorInterface
{
    public function __construct(
        private readonly CouponServiceInterface $couponService
    ) {}

    public function grant(ResourceIdentity $resource, UserInterface $user, array $context = []): GrantResult
    {
        try {
            $couponId = $this->couponService->issueCoupon(
                $resource->metadata['coupon_id'],
                $user->getUserIdentifier(),
                $resource->quantity
            );
            
            return new GrantResult(
                success: true,
                resourceId: $couponId,
                message: '优惠券发放成功'
            );
        } catch  (\Throwable $e) {
            return new GrantResult(
                success: false,
                message: $e->getMessage()
            );
        }
    }

    public function consume(ResourceIdentity $resource, UserInterface $user, array $context = []): ConsumeResult
    {
        try {
            $result = $this->couponService->useCoupon($resource->id, $user->getUserIdentifier(), $context);
            
            return new ConsumeResult(
                success: $result->isSuccess(),
                message: $result->getMessage()
            );
        } catch  (\Throwable $e) {
            return new ConsumeResult(
                success: false,
                message: $e->getMessage()
            );
        }
    }

    public function query(ResourceIdentity $resource, UserInterface $user): QueryResult
    {
        $coupon = $this->couponService->getCoupon($resource->id);
        
        return new QueryResult(
            exists: $coupon !== null,
            status: $coupon?->getStatus(),
            data: $coupon?->toArray() ?? []
        );
    }

    public function getSupportedTypes(): array
    {
        return ['coupon'];
    }

    public function supports(ResourceType $type): bool
    {
        return in_array($type->value, $this->getSupportedTypes());
    }
}
```

### 5.2 积分处理器

```php
namespace Tourze\ResourceManageBundle\Processor;

use Tourze\CreditBundle\Service\CreditServiceInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('resource_manage.processor')]
class CreditProcessor implements ResourceProcessorInterface
{
    public function __construct(
        private readonly CreditServiceInterface $creditService
    ) {}

    public function grant(ResourceIdentity $resource, UserInterface $user, array $context = []): GrantResult
    {
        $result = $this->creditService->addCredit(
            $user->getUserIdentifier(),
            $resource->metadata['amount'],
            $context['reason'] ?? '系统奖励',
            $resource->expireAt
        );
        
        return new GrantResult(
            success: $result->isSuccess(),
            resourceId: $result->getTransactionId(),
            message: $result->getMessage()
        );
    }

    public function consume(ResourceIdentity $resource, UserInterface $user, array $context = []): ConsumeResult
    {
        $result = $this->creditService->deductCredit(
            $user->getUserIdentifier(),
            $resource->metadata['amount'],
            $context['reason'] ?? '积分消费'
        );
        
        return new ConsumeResult(
            success: $result->isSuccess(),
            message: $result->getMessage()
        );
    }

    public function query(ResourceIdentity $resource, UserInterface $user): QueryResult
    {
        $balance = $this->creditService->getBalance($user->getUserIdentifier());
        
        return new QueryResult(
            exists: true,
            status: 'active',
            data: ['balance' => $balance]
        );
    }

    public function getSupportedTypes(): array
    {
        return ['credit'];
    }

    public function supports(ResourceType $type): bool
    {
        return in_array($type->value, $this->getSupportedTypes());
    }
}
```

### 5.3 抽奖次数处理器

```php
namespace Tourze\ResourceManageBundle\Processor;

use Tourze\LotteryBundle\Service\LotteryServiceInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('resource_manage.processor')]
class LotteryProcessor implements ResourceProcessorInterface
{
    public function __construct(
        private readonly LotteryServiceInterface $lotteryService
    ) {}

    public function grant(ResourceIdentity $resource, UserInterface $user, array $context = []): GrantResult
    {
        $result = $this->lotteryService->grantLotteryTimes(
            $user->getUserIdentifier(),
            $resource->metadata['lottery_id'],
            $resource->metadata['times'],
            $resource->expireAt
        );
        
        return new GrantResult(
            success: $result->isSuccess(),
            resourceId: $result->getRecordId(),
            message: $result->getMessage()
        );
    }

    public function consume(ResourceIdentity $resource, UserInterface $user, array $context = []): ConsumeResult
    {
        $result = $this->lotteryService->useLotteryTimes(
            $user->getUserIdentifier(),
            $resource->metadata['lottery_id'],
            1 // 每次消费1次抽奖机会
        );
        
        return new ConsumeResult(
            success: $result->isSuccess(),
            message: $result->getMessage()
        );
    }

    public function query(ResourceIdentity $resource, UserInterface $user): QueryResult
    {
        $times = $this->lotteryService->getUserLotteryTimes(
            $user->getUserIdentifier(),
            $resource->metadata['lottery_id']
        );
        
        return new QueryResult(
            exists: $times > 0,
            status: 'active',
            data: ['remaining_times' => $times]
        );
    }

    public function getSupportedTypes(): array
    {
        return ['lottery'];
    }

    public function supports(ResourceType $type): bool
    {
        return in_array($type->value, $this->getSupportedTypes());
    }
}
```

## 6. 使用示例

### 6.1 活动配置使用

```php
use Tourze\ResourceManageBundle\Manager\ResourceManagerInterface;
use Tourze\ResourceManageBundle\Model\Configuration\CouponConfiguration;
use Tourze\ResourceManageBundle\Model\Configuration\CreditConfiguration;
use Tourze\ResourceManageBundle\Model\Configuration\LotteryConfiguration;
use Symfony\Component\Security\Core\User\UserInterface;

class ActivityConfigService
{
    public function __construct(
        private readonly ResourceManagerInterface $resourceManager
    ) {}

    /**
     * 配置活动奖励
     */
    public function configureActivityRewards(): array
    {
        return [
            // 优惠券奖励：选择具体优惠券，配置数量
            new CouponConfiguration('coupon_new_user_10off', 2),
            
            // 积分奖励：填写积分数量和过期天数
            new CreditConfiguration(100, 30),
            
            // 抽奖次数奖励：选择抽奖活动，配置次数和过期时间
            new LotteryConfiguration('lottery_spring_festival', 3, 7)
        ];
    }

    /**
     * 发放活动奖励
     */
    public function grantActivityRewards(UserInterface $user, array $rewardConfigs): void
    {
        foreach ($rewardConfigs as $config) {
            $result = $this->resourceManager->grantFromConfiguration($config, $user, [
                'source' => 'activity_reward',
                'activity_id' => 'spring_festival_2024'
            ]);
            
            if (!$result->isSuccess()) {
                throw new \RuntimeException("奖励发放失败: {$result->getMessage()}");
            }
        }
    }
}
```

### 6.2 前端配置界面数据

```php
class ActivityConfigController
{
    public function __construct(
        private readonly ResourceManagerInterface $resourceManager
    ) {}

    /**
     * 获取可配置的资源类型
     */
    public function getResourceTypes(): array
    {
        return [
            CouponConfiguration::getConfigurationSchema(),
            CreditConfiguration::getConfigurationSchema(),
            LotteryConfiguration::getConfigurationSchema()
        ];
        
        // 返回结果示例：
        // [
        //     [
        //         'type' => 'coupon',
        //         'name' => '优惠券',
        //         'class' => 'Tourze\ResourceManageBundle\Model\Configuration\CouponConfiguration',
        //         'fields' => [
        //             'couponId' => [
        //                 'type' => 'select',
        //                 'label' => '选择优惠券',
        //                 'required' => true,
        //                 'source' => 'coupon_list'
        //             ],
        //             'quantity' => [...]
        //         ]
        //     ],
        //     [
        //         'type' => 'credit',
        //         'name' => '积分',
        //         'class' => 'Tourze\ResourceManageBundle\Model\Configuration\CreditConfiguration',
        //         'fields' => [
        //             'amount' => [
        //                 'type' => 'number',
        //                 'label' => '积分数量',
        //                 'required' => true,
        //                 'min' => 1
        //             ],
        //             'expireDays' => [...]
        //         ]
        //     ]
        // ]
    }

    /**
     * 根据前端提交的数据创建配置对象
     */
    public function createConfigurationFromRequest(array $data): ResourceConfiguration
    {
        return match ($data['type']) {
            'coupon' => new CouponConfiguration(
                $data['couponId'],
                $data['quantity'] ?? 1
            ),
            'credit' => new CreditConfiguration(
                $data['amount'],
                $data['expireDays'] ?? null
            ),
            'lottery' => new LotteryConfiguration(
                $data['lotteryId'],
                $data['times'],
                $data['expireDays'] ?? null
            ),
            default => throw new \InvalidArgumentException("不支持的资源类型: {$data['type']}")
        };
    }
}
```

### 6.3 类型安全的使用

```php
public function handleSpecialEvent(UserInterface $user): void
{
    // 类型安全的配置创建
    $couponConfig = new CouponConfiguration('special_event_coupon', 1);
    $creditConfig = new CreditConfiguration(500, 7); // 500积分，7天过期
    $lotteryConfig = new LotteryConfiguration('special_lottery', 2, 3); // 2次抽奖，3天过期
    
    // IDE 可以提供完整的类型提示和自动补全
    $configs = [$couponConfig, $creditConfig, $lotteryConfig];
    
    foreach ($configs as $config) {
        $result = $this->resourceManager->grantFromConfiguration($config, $user);
        
        if (!$result->isSuccess()) {
            throw new \RuntimeException("资源发放失败: {$result->getMessage()}");
        }
    }
}
```

## 7. 扩展新资源类型

要扩展新的资源类型，需要：

1. 创建对应的配置类
2. 实现资源处理器
3. 使用 `AutoconfigureTag` 自动注册

### 7.1 创建VIP天数配置类

```php
namespace YourBundle\Model\Configuration;

use Tourze\ResourceManageBundle\Model\ResourceConfiguration;
use Tourze\ResourceManageBundle\Model\ResourceType;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

readonly class VipDaysConfiguration extends ResourceConfiguration
{
    public function __construct(
        public int $days,
        public string $level = 'basic'
    ) {
        if ($days <= 0) {
            throw new \InvalidArgumentException('VIP天数必须大于0');
        }

        if (!in_array($level, ['basic', 'gold', 'platinum'])) {
            throw new \InvalidArgumentException('无效的VIP等级');
        }

        parent::__construct(
            type: ResourceType::create('vip_days', 'VIP天数'),
            quantity: 1
        );
    }

    public function toResourceIdentity(): ResourceIdentity
    {
        return new ResourceIdentity(
            id: uniqid('vip_'),
            type: $this->type,
            name: "VIP天数({$this->level})",
            metadata: [
                'days' => $this->days,
                'level' => $this->level
            ],
            quantity: $this->quantity
        );
    }

    public function getParameters(): array
    {
        return [
            'days' => $this->days,
            'level' => $this->level
        ];
    }

    public static function getConfigurationSchema(): array
    {
        return [
            'type' => 'vip_days',
            'name' => 'VIP天数',
            'class' => self::class,
            'fields' => [
                'days' => [
                    'type' => 'number',
                    'label' => 'VIP天数',
                    'required' => true,
                    'min' => 1
                ],
                'level' => [
                    'type' => 'select',
                    'label' => 'VIP等级',
                    'required' => true,
                    'options' => [
                        'basic' => '基础VIP',
                        'gold' => '黄金VIP',
                        'platinum' => '白金VIP'
                    ]
                ]
            ]
        ];
    }
}
```

### 7.2 实现VIP天数处理器

```php
namespace YourBundle\Processor;

use Tourze\ResourceManageBundle\Processor\ResourceProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('resource_manage.processor')]
class VipDaysProcessor implements ResourceProcessorInterface
{
    public function __construct(
        private readonly VipServiceInterface $vipService
    ) {}

    public function grant(ResourceIdentity $resource, UserInterface $user, array $context = []): GrantResult
    {
        $result = $this->vipService->extendVipDays(
            $user->getUserIdentifier(),
            $resource->metadata['days'],
            $resource->metadata['level']
        );
        
        return new GrantResult(
            success: $result->isSuccess(),
            resourceId: $result->getRecordId(),
            message: $result->getMessage()
        );
    }

    public function consume(ResourceIdentity $resource, UserInterface $user, array $context = []): ConsumeResult
    {
        // VIP天数通常不需要核销
        return new ConsumeResult(false, 'VIP天数不支持核销');
    }

    public function query(ResourceIdentity $resource, UserInterface $user): QueryResult
    {
        $vipInfo = $this->vipService->getUserVipInfo($user->getUserIdentifier());
        
        return new QueryResult(
            exists: $vipInfo !== null,
            status: $vipInfo?->getStatus(),
            data: $vipInfo?->toArray() ?? []
        );
    }

    public function getSupportedTypes(): array
    {
        return ['vip_days'];
    }

    public function supports(ResourceType $type): bool
    {
        return in_array($type->value, $this->getSupportedTypes());
    }
}
```

### 7.3 使用自定义资源类型

```php
use YourBundle\Model\Configuration\VipDaysConfiguration;

// 类型安全的创建
$vipConfig = new VipDaysConfiguration(30, 'gold');

// 发放资源
$result = $this->resourceManager->grantFromConfiguration($vipConfig, $user);

// 在活动配置中使用
public function configureVipRewards(): array
{
    return [
        new CouponConfiguration('vip_exclusive_coupon', 1),
        new CreditConfiguration(1000, null), // 永不过期的积分
        new VipDaysConfiguration(30, 'gold'), // 30天黄金VIP
    ];
}
```

## 8. 部署要求

- PHP 8.1+
- Symfony 6.0+
- MySQL/PostgreSQL（持久化）

---

## 总结

Resource Manage Bundle 通过统一的抽象和灵活的扩展机制，为各种资源权益的管理提供了完整的解决方案。核心设计理念：

1. **值对象模式**：ResourceType 既保证类型安全又允许无限扩展
2. **具体配置类**：每种资源类型都有专门的配置类，提供编译时类型检查和IDE支持
3. **处理器模式**：每种资源类型对应一个处理器，职责清晰
4. **自动配置**：使用 AutoconfigureTag 自动发现和注册处理器
5. **接口抽象**：统一的管理接口，屏蔽底层实现差异
6. **类型安全**：通过具体配置类确保参数类型正确，减少运行时错误

通过这种设计，管理员可以在前端界面灵活配置各种资源奖励，开发者可以享受完整的类型提示和编译时检查，同时任何第三方包都可以轻松扩展新的资源类型。

### 主要优势

- **类型安全**：具体配置类提供编译时类型检查
- **IDE友好**：完整的自动补全和类型提示
- **参数验证**：在构造函数中进行参数验证，及早发现错误
- **可读性强**：每种资源类型的配置参数一目了然
- **易于扩展**：新增资源类型只需创建配置类和处理器
- **维护性好**：配置结构和处理逻辑分离，职责清晰
