<?php

namespace Biig\Component\Domain\Event;

use Biig\Component\Domain\Model\ModelInterface;
use Biig\Component\Domain\Rule\DomainRuleInterface;
use Biig\Component\Domain\Rule\PostPersistDomainRuleInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface DomainEventDispatcherInterface extends EventDispatcherInterface
{
    public function addDomainRule(DomainRuleInterface $rule): void;

    public function addPostPersistDomainRuleInterface(PostPersistDomainRuleInterface $rule): void;

    public function persistModel(ModelInterface $model): void;

    public function getDelayedListeners(): array;
}
