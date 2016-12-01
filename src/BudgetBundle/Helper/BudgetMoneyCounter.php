<?php
namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

/**
 * Class BudgetMoneyCounter
 * @package BudgetBundle\Helper
 */
class BudgetMoneyCounter
{
    /**
     * @param ArrayCollection|\IteratorAggregate $arrayCollection
     * @return float
     * @throws Exception
     */
    public static function countBudget($arrayCollection)
    {
        if (!is_a($arrayCollection,ArrayCollection::class)) {
            throw new Exception('Not array collection');
        }
        $sum = 0.0;
        foreach ($arrayCollection as $item) {
            /** @var Budget $item */
            $sum += (float)$item->getMoney();
        }

        return $sum;
    }
}