<?php

namespace Tourze\ResourceManageBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\EnumExtra\SelectDataFetcher;
use Tourze\ResourceManageBundle\Exception\UnknownResourceException;

#[Autoconfigure(public: true)]
class ResourceManager implements SelectDataFetcher
{
    public function __construct(
        #[TaggedIterator(ResourceProvider::TAG_NAME)] private readonly iterable $services,
    ) {
    }

    public function genSelectData(): iterable
    {
        foreach ($this->services as $service) {
            /* @var ResourceProvider $service */
            yield [
                'label' => $service->getLabel(),
                'text' => $service->getLabel(),
                'value' => $service->getCode(),
                'name' => $service->getLabel(),
            ];
        }
    }

    /**
     * 发送奖励
     */
    public function send(UserInterface $user, string $type, string $typeId, string $amount, ?float $expireDay = null, ?\DateTimeInterface $expireTime = null): void
    {
        foreach ($this->services as $service) {
            /** @var ResourceProvider $service */
            if ($service->getCode() === $type) {
                $identity = $service->findIdentity($typeId);
                $service->sendResource($user, $identity, $amount, $expireDay, $expireTime);

                return;
            }
        }
        throw new UnknownResourceException('不支持的资源类型');
    }
}
