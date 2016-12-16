<?php

namespace Tests\BudgetBundle\Form\Type;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Form\Type\BudgetType;
use BudgetBundle\Form\Type\ExpenseType;
use CategoryBundle\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Doctrine\ORM\Mapping\ClassMetadata;


class BudgetTypeTest extends TypeTestCase
{
    private $entityManager;

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();

        parent::setUp();

    }

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

        $mockEntityManager ->expects($this->any())->method('getClassMetadata')
            ->withAnyParameters()
            ->will($this->returnValue(new ClassMetadata('entity')));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEntityManager ->expects($this->any())->method('getRepository')
            ->withAnyParameters()
            ->will($this->returnValue($repo));

        $repo->expects($this->any())->method('find')
            ->withAnyParameters()
            ->will($this->returnValue($category));


        $entityType = new EntityType($mockRegistry);



        return array(new PreloadedExtension(array(
            'entity' => $entityType,
        ), array()));
    }


    /**
     * Currently there isn't any option to test field created from EntityType,
     * Multiple solutions were tried, this one at least helps to test form almost fully.
     *
     */
    public function testSubmitValidData()
    {

        $formData = [
            'name' => 'test',
            'dateTime' => '2016-12-15 00:00',
            'money' => '10',
        ];

        $budget = new Budget();
        $category = new Category();
        $category->setId(10);
        $category->setName('category');


        $form = $this->factory->create(BudgetType::class,$budget,['categories' => [$category]]);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var Budget $budget */
        $budget = $form->getData();

        $this->assertEquals('test', $budget->getName());
        $this->assertEquals('2016-12-15 00:00', $budget->getDateTime()->format('Y-m-d H:i'));
        $this->assertEquals('10', $budget->getMoney());

    }
}