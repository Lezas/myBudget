<?php

namespace Tests\BudgetBundle\Entity;

use Tests\TestCases\FunctionalTestCase;

/**
 * Class BudgetTest
 */
class BudgetTest extends FunctionalTestCase
{
    public function testGetId()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'expense']);
        $expenses = $category->getExpense();
        $expense = $expenses[0];

        $this->assertInternalType('integer',$expense->getId());
    }
}