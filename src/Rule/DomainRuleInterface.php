<?php

namespace Biig\Component\Domain\Rule;

use Biig\Component\Domain\Event\DomainEvent;

/**
 * @template T of DomainEvent
 * @extends RuleInterface<T>
 */
interface DomainRuleInterface extends RuleInterface
{
    /**
     * Returns an array of event or a string it listens on.
     *
     * @return list<string>|class-string<T>|string
     */
    public function on(): array|string;
}
