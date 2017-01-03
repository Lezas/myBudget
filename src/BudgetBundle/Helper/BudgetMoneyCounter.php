<?php
namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Budget;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Finder\Tests\Iterator\Iterator;

/**
 * Class BudgetMoneyCounter
 * @package BudgetBundle\Helper
 */
class BudgetMoneyCounter
{
    /**
     * @param array $arrayCollection
     * @return float
     */
    public static function countBudget(array $arrayCollection)
    {
        $sum = 0.0;
        foreach ($arrayCollection as $item) {
            /** @var Budget $item */
            $sum += (float)$item->getMoney();
        }

        return $sum;
    }
}