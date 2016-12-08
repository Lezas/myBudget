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

        $this->assertEquals(1, count($data));
        $this->assertEquals(1, count($data[0]));
        /** @var Category $afterCategory */
        $afterCategory = $data[0][0];
        $this->assertEquals($category->getName(), $afterCategory->getName());

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
        /** @var Category $categoryWithoutParent */
        $categoryWithoutParent = $grouped[0][0];
        /** @var Category $parentCategory */
        $parentCategory = $grouped[$parent->getId()][0];
        $grouped = $grouped->toArray();

        $this->assertEquals(2, count($grouped));
        $this->assertEquals(1, count($grouped[0]));
        $this->assertArrayHasKey($parent->getId(), $grouped);
        $this->assertEquals(1, count($grouped[$parent->getId()]));
        $this->assertEquals($category->getName(), $parentCategory->getName());
        $this->assertEquals($categoryWithoutParent->getId(), $parent->getId());

    }

    public function testGroupByParent_multipleParentsFound()
    {
        $groupingHelper = new GroupingHelper();

        $category1 = new Category();
        $category1->setId(1);

        $category2 = new Category();
        $category2->setId(2);

        $category3 = new Category();
        $category3->setId(3);

        $category4 = new Category();
        $category4->setId(4);



        $parent1 = new Category();
        $parent1->setId(11);
        $category1->setParent($parent1);
        $category2->setParent($parent1);

        $parent2 = new Category();
        $parent2->setId(12);
        $category3->setParent($parent2);

        $categories = [];
        $categories[] = $category1;
        $categories[] = $category2;
        $categories[] = $category3;
        $categories[] = $category4;
        $categories[] = $parent1;
        $categories[] = $parent2;

        $grouped = $groupingHelper->groupByParent($categories);

        $grouped = $grouped->toArray();

        $this->assertEquals(3, count($grouped));
        $this->assertEquals(3, count($grouped[0]));
        $this->assertArrayHasKey($parent1->getId(), $grouped);
        $this->assertArrayHasKey($parent2->getId(), $grouped);
        $this->assertEquals(2, count($grouped[$parent1->getId()]));
        $this->assertEquals(1, count($grouped[$parent2->getId()]));

        $this->assertEquals($category1->getId(), $grouped[$parent1->getId()][0]->getId());
        $this->assertEquals($category2->getId(), $grouped[$parent1->getId()][1]->getId());
        $this->assertEquals($category3->getId(), $grouped[$parent2->getId()][0]->getId());

    }

    public function testGroupByParent_methodReturnsArrayCollection()
    {
        $groupingHelper = new GroupingHelper();
        $categories = [];
        $data = $groupingHelper->groupByParent($categories);

        $this->assertInstanceOf(ArrayCollection::class, $data);
    }
}