<?php

namespace Tests\CategoryBundle\Repository;

use CategoryBundle\Entity\Category;
use CategoryBundle\Repository\CategoryRepositoryService;
use Tests\TestCase\FunctionalTestCase;

/**
 * Class CategoryRepositoryServiceTest
 * @package Tests\CategoryBundle\Repository
 */
class CategoryRepositoryServiceTest extends FunctionalTestCase
{
    public function testGetIncomeCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $catRepService = new CategoryRepositoryService(static::$kernel->getContainer()
            ->get('doctrine'));

        $categories = $catRepService->getAllUserIncomeCategories($user);
        /** @var Category $category */
        $category = $categories[0];
        $this->assertCount(1, $categories);
        $this->assertEquals('income',$category->getName());
    }

    public function testGetExpenseCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $catRepService = new CategoryRepositoryService(static::$kernel->getContainer()
            ->get('doctrine'));

        $categories = $catRepService->getAllUserExpenseCategories($user);
        /** @var Category $category */
        $category = $categories[0];
        $this->assertCount(1, $categories);
        $this->assertEquals('expense',$category->getName());
    }

}