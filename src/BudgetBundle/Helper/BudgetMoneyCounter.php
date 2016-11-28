<?php
namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class BudgetMoneyCounter
 * @package BudgetBundle\Helper
 */
class BudgetMoneyCounter
{
    /**
     * @param ArrayCollection|\IteratorAggregate $arrayCollection
     * @return float
     */
    public static function countBudget(\IteratorAggregate $arrayCollection)
    {
        $sum = 0.0;
        foreach ($arrayCollection as $item) {
            /** @var Budget $item */
            $sum += (float)$item->getMoney();
        }

        return $sum;
    }
}