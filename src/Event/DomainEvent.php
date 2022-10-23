<?php

namespace Biig\Component\Domain\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

class DomainEvent extends GenericEvent
{
    private ?Event $originalEvent;

    /**
     * If true, it will be raised after doctrine flush.
     */
    private bool $delayed;

    public function __construct($subject = null, $arguments = [], Event $originalEvent = null)
    {
        parent::__construct($subject, $arguments);
        $this->originalEvent = $originalEvent;
        $this->delayed = false;
    }

    public function getOriginalEvent(): ?Event
    {
        return $this->originalEvent;
    }

    public function isDelayed(): bool
    {
        return $this->delayed;
    }

    /**
     * @internal
     */
    public function setDelayed()
    {
        $this->delayed = true;
    }
}
