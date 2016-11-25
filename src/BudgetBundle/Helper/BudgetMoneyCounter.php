<?php
namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;
use Doctrine\Common\Collections\ArrayCollection;

class BudgetMoneyCounter
{
    /**
     * @param ArrayCollection $arrayCollection
     * @return float
     */
    public static function countBudget(ArrayCollection $arrayCollection)
    {
        $sum = 0.0;
        foreach ($arrayCollection as $item) {
            /** @var Budget $item */
            $sum += (float)$item->getMoney();
        }

        return $sum;
    }
}