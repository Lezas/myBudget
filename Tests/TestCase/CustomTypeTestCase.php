<?php

namespace Tests\TestCase;

use CategoryBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class CategoryTypeTest
 * @package Tests\CategoryBundle\Form
 */
class CustomTypeTestCase extends TypeTestCase
{
    private $entityManager;

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $category = new Category();
        $category->setId(10);
        $category->setName('category');

        // Mock the FormType: entity
        $mockEntityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRegistry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRegistry->expects($this->any())->method('getManagerForClass')
            ->will($this->returnValue($mockEntityManager));

        $mockEntityManager->expects($this->any())->method('getClassMetadata')
            ->withAnyParameters()
            ->will($this->returnValue(new ClassMetadata('entity')));

        $entityType = new EntityType($mockRegistry);

        return [
            new PreloadedExtension(['entity' => $entityType,], [])
        ];
    }


}