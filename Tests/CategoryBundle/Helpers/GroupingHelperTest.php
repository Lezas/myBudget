<?php

namespace Tests\CategoryBundle\Helper;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Helper\DataFormatter;
use CategoryBundle\Entity\Category;
use CategoryBundle\Helpers\GroupingHelper;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class GroupingHelperTest
 * @package Tests\CategoryBundle\Helper
 */
class GroupingHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGroupByParent_noParentsFound()
    {
        $category = new Category();
        $category->setName("Category1");
        $groupingHelper = new GroupingHelper();
        $categories = [];
        $categories[] = $category;

        $data = $groupingHelper->groupByParent($categories);

        $this->assertEquals(1,count($data));
        $this->assertEquals(1,count($data[0]));
        /** @var Category $afterCategory */
        $afterCategory = $data[0][0];
        $this->assertEquals($category->getName(),$afterCategory->getName());

    }

    public function testGroupByParent_oneParentFound()
    {
        $category = new Category();
        $category->setName("Category1");
        $groupingHelper = new GroupingHelper();

        $parent = new Category();
        $parent->setId(11);
        $category->setParent($parent);
        $categories = [];
        $categories[] = $category;
        $categories[] = $parent;

        $grouped = $groupingHelper->groupByParent($categories);

        $grouped = $grouped->toArray();
        $this->assertEquals(2,count($grouped));
        $this->assertEquals(1,count($grouped[0]));
        $this->assertArrayHasKey($parent->getId(),$grouped);
        $this->assertEquals(1,count($grouped[$parent->getId()]));
        $this->assertEquals($category->getName(),$grouped[$parent->getId()][0]->getName());

        /** @var Category $categoryWithoutParent */
        $categoryWithoutParent = $grouped[0][0];

        $this->assertEquals($categoryWithoutParent->getId(),$parent->getId());

    }
}