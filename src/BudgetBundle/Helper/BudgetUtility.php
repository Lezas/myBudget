<?php

namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;

/**
 * Class BudgetUtility
 */
class BudgetUtility
{
    /**
     * @param $budget
     *
     * @return float|int
     */
    public function sumBudget(array $budget)
    {
        $total = 0;
        foreach ($budget as $item) {
            if (is_a($item, Budget::class)) {
                /** @var $budget Budget */
                $total += (float)$item->getMoney();
            }
        }

        return $total;
    }
}