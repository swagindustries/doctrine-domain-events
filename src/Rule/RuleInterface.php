<?php

namespace Biig\Component\Domain\Rule;

use Biig\Component\Domain\Event\DomainEvent;

/**
 * Interface RuleInterface.
 *
 * You should never implement only this interface.
 * Use `DomainRuleInterface` or `DomainDelayedRuleInterface`.
 *
 * @template T of DomainEvent
 */
interface RuleInterface
{
    /**
     * @param T $event
     */
    public function execute(DomainEvent $event): void;
}
