<?php

namespace App\Interface\GraphQL\Resolver;

use ApiPlatform\GraphQl\Resolver\MutationResolverInterface;
use App\Domain\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGroupNameResolver implements MutationResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function __invoke(?object $item, array $context): Group
    {
        if (!$item instanceof Group) {
            throw new \InvalidArgumentException('Invalid Teacher entity.');
        }

        // Проверяем, что передано имя
        if (!isset($context['args']['input']['name'])) {
            throw new \InvalidArgumentException('The "name" field is required.');
        }

        // Обновляем имя
        $item->setName($context['args']['input']['name']);
        $this->em->flush();

        return $item;
    }
}
