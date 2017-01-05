<?php

namespace Tests\BudgetBundle\Helper\DateTime;


use BudgetBundle\Entity\Budget;
use BudgetBundle\Helper\BudgetMoneyCounter;
use BudgetBundle\Helper\DateTime\DateTimeHelper;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class BudgetMoneyCounterTest
 * @package Tests\BudgetBundle\Helper
 */
class DateTimeHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFirstDay()
    {
        $helper = new DateTimeHelper();
        $date = new \DateTime('2017-01-25 00:00');

        $firstDate = $helper->getFirstDayOfMonth($date);

        $this->assertEquals('2017-01-01', $firstDate->format('Y-m-d'));
    }

    public function testGetLastDay()
    {
        $helper = new DateTimeHelper();
        $date = new \DateTime('2017-01-25 00:00');

        $lastDay = $helper->getLastDayOfMonth($date);

        $this->assertEquals('2017-01-31', $lastDay->format('Y-m-d'));
    }
}