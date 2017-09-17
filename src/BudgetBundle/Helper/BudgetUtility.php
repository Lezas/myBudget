<?php
/**
 * Created by Lezas.
 * Date: 2017-09-15
 * Time: 23:21
 */

namespace BudgetBundle\Helper;


use BudgetBundle\Entity\Budget;

/**
 * Class BudgetUtility
 * @package BudgetBundle\Helper
 */
class BudgetUtility
{
    /**
     * @param $budget
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