<?php
declare(strict_types=1);

class Root extends \Biig\Component\Domain\Model\DomainModel
{
}

class Intermediate extends Root
{
}

class ComplexClassHierarchyDomainModel extends Intermediate
{
    private $id = 12;
}

function getComplexModel()
{
    return new ComplexClassHierarchyDomainModel();
}
