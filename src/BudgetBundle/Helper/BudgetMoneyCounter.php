<?php
namespace BudgetBundle\Helper;

use Doctrine\Common\Collections\ArrayCollection;

class BudgetMoneyCounter
{
    public static function countBudget(ArrayCollection $arrayCollection)
    {
        $sum = 0;
        foreach ($arrayCollection as $item) {
            $sum += (float)$item->getMoney();
        }

        return $sum;
    }
}