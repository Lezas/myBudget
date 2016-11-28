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

        $this->assertEquals(1,count($return));
        $this->assertEquals(300,$return[0][1]);

        $b = new Budget();
        $b->setMoney('200.99')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $b = new Budget();
        $b->setMoney('200.05')
            ->setDateTime(new \DateTime('2016-01-02'));
        $arr[] = $b;

        $return = DataFormatter::groupByDay($arr);

        $this->assertEquals(2,count($return));
        $this->assertEquals(300,$return[0][1]);
        $this->assertEquals('2016-01-01',$return[0][0]);
        $this->assertEquals(401.04,$return[1][1]);
        $this->assertEquals('2016-01-02',$return[1][0]);

    }
}