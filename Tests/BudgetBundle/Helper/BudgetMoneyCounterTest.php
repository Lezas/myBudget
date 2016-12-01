<?php

namespace Tests\BudgetBundle\Helper;


use BudgetBundle\Entity\Budget;
use BudgetBundle\Helper\BudgetMoneyCounter;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class BudgetMoneyCounterTest
 * @package Tests\BudgetBundle\Helper
 */
class BudgetMoneyCounterTest extends \PHPUnit_Framework_TestCase
{
    public function testCountBudget()
    {
        try {
            $budget = new Budget();
            $budget->setMoney(55);
            $arr = [];
            $arr[] = $budget;
            $budget = new Budget();
            $budget->setMoney(50);
            $arr[] = $budget;
            $result = BudgetMoneyCounter::countBudget($arr);

            $this->assertEquals(105, $result);
        } catch (\Exception $e) {
            $this->assertEquals("Not array collection", $e->getMessage());
        }


    }
}