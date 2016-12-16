<?php

namespace Tests\BudgetBundle\Form\Type;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Form\Type\BudgetType;
use CategoryBundle\Entity\Category;
use Tests\TestCase\CustomTypeTestCase;

/**
 * Class BudgetTypeTest
 * @package Tests\BudgetBundle\Form\Type
 */
class BudgetTypeTest extends CustomTypeTestCase
{

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

        $form = $this->factory->create(BudgetType::class, $budget, ['categories' => [$category]]);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var Budget $budget */
        $budget = $form->getData();

        $this->assertEquals('test', $budget->getName());
        $this->assertEquals('2016-12-15 00:00', $budget->getDateTime()->format('Y-m-d H:i'));
        $this->assertEquals('10', $budget->getMoney());

        $form = $this->factory->create(BudgetType::class);

        // submit the data to the form directly
        $form->submit($formData);
        $data = $form->getData();

        $this->assertEquals('test', $data['name']);
        $this->assertEquals('2016-12-15 00:00', $data['dateTime']->format('Y-m-d H:i'));
        $this->assertEquals('10', $data['money']);
    }
}