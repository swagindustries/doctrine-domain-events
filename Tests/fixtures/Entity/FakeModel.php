<?php
namespace Biig\Component\Domain\Tests\fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fake_model')]
class FakeModel implements \Biig\Component\Domain\Model\ModelInterface
{
    use \Biig\Component\Domain\Model\DomainModelTrait;

    #[ORM\Id()]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(nullable: true)]
    private $foo;

    #[ORM\OneToOne(targetEntity: FakeModelRelation::class)]
    private FakeModelRelation|null $related = null;

    private $something;

    public function hasDispatcher()
    {
        return null !== $this->dispatcher;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getSomething(): mixed
    {
        return $this->something;
    }

    public function setSomething(string $something)
    {
        $this->something = $something;
    }

    public function setFoo(string $foo)
    {
        $this->foo = $foo;
    }

    public function getRelated(): ?FakeModelRelation
    {
        return $this->related;
    }

    public function setRelated(?FakeModelRelation $related): void
    {
        $this->related = $related;
    }

    /**
     * Raise a domain event.
     */
    public function doAction()
    {
        $this->dispatch(new \Biig\Component\Domain\Event\DomainEvent($this), 'previous_action');
        $this->dispatch(new \Biig\Component\Domain\Event\DomainEvent($this), 'action');
    }
}
