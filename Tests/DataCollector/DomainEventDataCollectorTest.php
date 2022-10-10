<?php

namespace Biig\Component\Domain\Tests\DataCollector;


use Biig\Component\Domain\DataCollector\DomainEventDataCollector;
use Biig\Component\Domain\Debug\TraceableDomainEventDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Prophecy\PhpUnit\ProphecyTrait;

class DomainEventDataCollectorTest extends TestCase
{
    use ProphecyTrait;

    public function testItCollect()
    {
        $dispatcher = $this->prophesize(TraceableDomainEventDispatcher::class);
        $stack = $this->prophesize(RequestStack::class);
        $stack->getMasterRequest()->shouldBeCalled()->willReturn($request = $this->prophesize(Request::class)->reveal());

        $collector = new DomainEventDataCollector($dispatcher->reveal(), $stack->reveal());
        $collector->collect($request, $this->prophesize(Response::class)->reveal());
    }
}
