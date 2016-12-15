<?php

namespace Tests\MainBundle\Entity;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use CategoryBundle\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

/**
 * Class UserTest
 * @package Tests\MainBundle\Entity
 */
class UserTest extends WebTestCase
{

    public function testCategoryFunctionality()
    {
        $user = new User();
        $category = new Category();
        $category->setName('category1');

        $user->addCategory($category);

        $this->assertCount(1,$user->getCategories());
        $this->assertEquals($user,$category->getUser());

        $category2 = new Category();
        $category2->setName('Category2');

        $user->addCategory($category2);

        $this->assertCount(2,$user->getCategories());
        $this->assertEquals($user,$category2->getUser());

        $user->removeCategory($category);

        $this->assertCount(1,$user->getCategories());
        $this->assertEquals(null,$category->getUser());
    }

    public function testIncomeFunctionality()
    {
        $user = new User();
        $income = new Income();
        $income->setName('Income1');

        $user->addIncome($income);

        $this->assertCount(1,$user->getIncome());
        $this->assertEquals($user,$income->getUser());

        $income2 = new Income();
        $income2->setName('Income2');

        $user->addIncome($income2);

        $this->assertCount(2,$user->getIncome());
        $this->assertEquals($user,$income2->getUser());

        $user->removeIncome($income);

        $this->assertCount(1,$user->getIncome());
        $this->assertEquals(null,$income->getUser());
    }

    public function testExpenseFunctionality()
    {
        $user = new User();
        $expense = new Expenses();
        $expense->setName('Expense1');

        $user->addExpense($expense);

        $this->assertCount(1,$user->getExpense());
        $this->assertEquals($user,$expense->getUser());

        $expense2 = new Expenses();
        $expense2->setName('Expense2');

        $user->addExpense($expense2);

        $this->assertCount(2,$user->getExpense());
        $this->assertEquals($user,$expense2->getUser());

        $user->removeExpense($expense);

        $this->assertCount(1,$user->getExpense());
        $this->assertEquals(null,$expense->getUser());
    }
}