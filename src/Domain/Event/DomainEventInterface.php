<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface DomainEventInterface extends \JsonSerializable
{
    public function getEventName(): string;
}
