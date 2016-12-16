<?php

namespace Tests\CategoryBundle\Form;

use CategoryBundle\Entity\Category;
use CategoryBundle\Form\Type\CategoryType;
use Tests\TestCase\CustomTypeTestCase;

/**
 * Class CategoryTypeTest
 * @package Tests\CategoryBundle\Form
 */
class CategoryTypeTest extends CustomTypeTestCase
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
            'type' => 'expense',
            'parent' => '15',
            'valid' => 1,
        ];

        $category = new Category();
        $category->setId(10);
        $category->setName('category');

        $form = $this->factory->create(CategoryType::class, $category, ['parents' => [$category]]);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var Category $category */
        $category = $form->getData();

        $this->assertEquals('test', $category->getName());
        $this->assertEquals('expense', $category->getType());
        $this->assertEquals('1', $category->getValid());

        $form = $this->factory->create(CategoryType::class);

        // submit the data to the form directly
        $form->submit($formData);
        $data = $form->getData();

        $this->assertEquals('test', $data['name']);
        $this->assertEquals('expense', $data['type']);
        $this->assertEquals('1', $data['valid']);
    }
}