<?php
declare(strict_types=1);

namespace Biig\Component\Domain\Exception;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;

class FlushedEntityDoesntContainsDispatcherException extends \LogicException
{
    public function __construct($idsAndValue, $className, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $identity = "'$className with $idsAndValue";

        $message = sprintf(
            'The entity %s has been flushed and it doesn\'t contains an instance of ' . DomainEventDispatcherInterface::class . ' for the "dispatcher" property !' .
            ' See https://github.com/swagindustries/doctrine-domain-events#drawbacks , it exlain how to to it.',
            $identity
        );
        parent::__construct($message, $code, $previous);
    }
}
