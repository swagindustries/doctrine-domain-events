<?php

namespace Biig\Component\Domain\Tests\fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fake_model_relation')]
class FakeModelRelation implements \Biig\Component\Domain\Model\ModelInterface
{
    use \Biig\Component\Domain\Model\DomainModelTrait;

    #[ORM\Id()]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column]
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function hasDispatcher()
    {
        return null !== $this->dispatcher;
    }
}
