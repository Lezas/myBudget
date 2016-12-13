<?php

namespace Tests\BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Helper\DataFormatter;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class DataFormatterTest
 * @package Tests\BudgetBundle\Helper
 */
class DataFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testGroupByDay()
    {
        $arr = [];

        $b = new Budget();
        $b->setMoney('100')
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200')
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;

        $return = DataFormatter::groupByDay($arr);

        $this->assertEquals(1, count($return));
        $this->assertEquals(300, $return[0][1]);

        $b = new Budget();
        $b->setMoney('200.99')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200.05')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $return = DataFormatter::groupByDay($arr);

        $this->assertEquals(2, count($return));
        $this->assertEquals(300, $return[0][1]);
        $this->assertEquals('2016-01-01', $return[0][0]);
        $this->assertEquals(401.04, $return[1][1]);
        $this->assertEquals('2016-01-02', $return[1][0]);

    }

    public function testConnectData()
    {
        $arr = [];

        $b = new Budget();
        $b->setMoney('100')
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200')
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200.99')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200.05')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $filteredBudget1 = DataFormatter::groupByDay($arr);

        $arr = [];

        $b = new Budget();
        $b->setMoney('50')
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('75')
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200.95')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200.05')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $filteredBudget2 = DataFormatter::groupByDay($arr);

        $data = DataFormatter::connectData($filteredBudget1, $filteredBudget2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(300, $data[0][1]);
        $this->assertEquals(125, $data[0][2]);
        $this->assertEquals('2016-01-01', $data[0][0]);
        $this->assertEquals(401.04, $data[1][1]);
        $this->assertEquals(401.00, $data[1][2]);
        $this->assertEquals('2016-01-02', $data[1][0]);
    }

    public function testConnectData_allDifferent()
    {
        $arr = [];

        $b = new Budget();
        $b->setMoney(100)
            ->setDateTime(new \DateTime('2016-01-01'));
        $arr[] = $b;


        $b = new Budget();
        $b->setMoney(200.05)
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $filteredBudget1 = DataFormatter::groupByDay($arr);

        $b = new Budget();
        $b->setMoney(50)
            ->setDateTime(new \DateTime('2016-01-03'));
        $arr[] = $b;


        $b = new Budget();
        $b->setMoney(255.05)
            ->setDateTime(new \DateTime('2016-01-04'));
        $arr[] = $b;

        $filteredBudget2 = DataFormatter::groupByDay($arr);

        $data = DataFormatter::connectData($filteredBudget1, $filteredBudget2);

        $this->assertEquals(4, count($data));
        $this->assertEquals(100, $data[0][1]);
        $this->assertEquals(100, $data[0][2]);
        $this->assertEquals(200.05, $data[1][1]);
        $this->assertEquals(200.05, $data[1][2]);
        $this->assertEquals(0, $data[2][1]);
        $this->assertEquals(50, $data[2][2]);
        $this->assertEquals(0, $data[3][1]);
        $this->assertEquals(255.05, $data[3][2]);
        $this->assertEquals('2016-01-01', $data[0][0]);
        $this->assertEquals('2016-01-02', $data[1][0]);
        $this->assertEquals('2016-01-03', $data[2][0]);
        $this->assertEquals('2016-01-04', $data[3][0]);

    }
}