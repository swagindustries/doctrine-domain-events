<?php

namespace Biig\Component\Domain\Debug;

use Biig\Component\Domain\Event\DelayedListener;
use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\ModelInterface;
use Biig\Component\Domain\Rule\DomainRuleInterface;
use Biig\Component\Domain\Rule\PostPersistDomainRuleInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Stopwatch\Stopwatch;

class TraceableDomainEventDispatcher extends TraceableEventDispatcher implements DomainEventDispatcherInterface
{
    /**
     * @var array
     */
    protected $eventsFired;

    /**
     * @var DomainEventDispatcherInterface
     */
    protected $decorated;

    /**
     * @var DelayedListener[]
     */
    protected $delayedListenersCalled;

    /**
     * TraceableDomainEventDispatcher constructor.
     */
    public function __construct(DomainEventDispatcherInterface $dispatcher)
    {
        $this->eventsFired = [];
        $this->delayedListenersCalled = [];
        $this->decorated = $dispatcher;
        parent::__construct($dispatcher, new Stopwatch(), new NullLogger());
    }

    public function addDomainRule(DomainRuleInterface $rule)
    {
        return $this->decorated->addDomainRule($rule);
    }

    public function addPostPersistDomainRuleInterface(PostPersistDomainRuleInterface $rule)
    {
        return $this->decorated->addPostPersistDomainRuleInterface($rule);
    }

    public function persistModel(ModelInterface $model)
    {
        /** @var DelayedListener $listener */
        foreach ($this->decorated->getDelayedListeners() as $listener) {
            $eventName = $listener->getEventName();

            if ($listener->shouldOccur($model)) {
                if (!$listener instanceof WrappedDelayedListener) {
                    $listener = new WrappedDelayedListener($listener);
                }

                $this->delayedListenersCalled[] = $listener->getInfo($eventName);
            }
        }

        return $this->decorated->persistModel($model);
    }

    public function getEventsFired(): array
    {
        return $this->eventsFired;
    }

    /**
     * @return DelayedListener[]
     */
    public function getDelayedListeners(): array
    {
        return $this->decorated->getDelayedListeners();
    }

    /**
     * @return DelayedListener[]
     */
    public function getDelayedListenersCalled(): array
    {
        return $this->delayedListenersCalled;
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param object      $event     The event to pass to the event handlers/listeners
     * @param string|null $eventName The name of the event to dispatch. If not supplied,
     *                               the class of $event should be used instead.
     *
     * @return object The passed $event MUST be returned
     */
    public function dispatch(object $event, ?string $eventName = null): object
    {
        $eventName = $eventName ?? get_class($event);
        $this->eventsFired[] = $eventName;

        return parent::dispatch($event, $eventName);
    }
}
