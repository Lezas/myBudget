<?php
namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;

/**
 * Class BudgetMoneyCounter
 */
class BudgetMoneyCounter
{
    /**
     * @param array $arrayCollection
     *
     * @return float
     */
    public static function countBudget($arrayCollection)
    {
        $sum = 0.0;
        foreach ($arrayCollection as $item) {
            if (is_a($item, Budget::class)) {
                /** @var Budget $item */
                $sum += (float)$item->getMoney();
            }
        }

        return $sum;
    }
}