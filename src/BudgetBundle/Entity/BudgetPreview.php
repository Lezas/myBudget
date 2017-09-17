<?php
/**
 * Created by Lezas.
 * Date: 2017-09-16
 * Time: 12:31
 */

namespace BudgetBundle\Entity;

use BudgetBundle\Helper\BudgetMoneyCounter;
use BudgetBundle\Repository\BudgetCategoryRepository;
use BudgetBundle\Repository\BudgetRepository;
use CategoryBundle\Entity\Category;


/**
 * Class BudgetPreview
 * @package BudgetBundle\Entity
 */
class BudgetPreview
{
    protected $budgetData = [];
    protected $budgetCategoryRepository;
    protected $budgetRepository;
    protected $budgetCounter;

    /**
     * BudgetPreview constructor.
     * @param BudgetCategoryRepository $budgetCategoryRepository
     * @param BudgetRepository $budgetRepository
     * @param BudgetMoneyCounter $budgetCounter
     */
    public function __construct(BudgetCategoryRepository $budgetCategoryRepository, BudgetRepository $budgetRepository, BudgetMoneyCounter $budgetCounter)
    {
        $this->budgetCategoryRepository = $budgetCategoryRepository;
        $this->budgetRepository = $budgetRepository;
        $this->budgetCounter = $budgetCounter;
    }

    /**
     * @param $category
     * @param $categoryBudgets
     * @param $total
     */
    public function addBudget($category, $categoryBudgets, $total)
    {
        $this->budgetData[] = [
            'category' => $category,
            'categoryBudget' => $categoryBudgets,
            'total' => $total
        ];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->budgetData;
    }

    /**
     * @return float
     */
    public function getTotalMoneyCount()
    {
        $total = 0;
        foreach ($this->budgetData as $budgetDatum) {
            $total += $budgetDatum['total'];
        }

        return $total;
    }

    /**
     * @param $user
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public function calculateBudget($user, $date_from, $date_to)
    {
        $budgetCategories = $this->budgetCategoryRepository->getAllUserBudgetCategories($user);

        foreach ($budgetCategories as $category) {
            $categoryIncome = $this->budgetRepository->getByDateRangeAndCategories($user, $date_from->format('Y-m-d H:i'), $date_to->format('Y-m-d H:i'), [$category->getId()]);
            $total = $this->budgetCounter->countBudget($categoryIncome);
            if ($total > 0) {
                $this->addBudget($category, $categoryIncome, $total);
            }
        }

        $budgetWithNoCat = $this->budgetRepository->getByDateRangeWithoutCategories($user, $date_from, $date_to);
        $total = $this->budgetCounter->countBudget($budgetWithNoCat);
        $NoCat = new Category();
        $NoCat->setName('Without Category');
        if ($total > 0) {
            $this->addBudget($NoCat, $budgetWithNoCat, $total);
        }

        return $this->getData();
    }
}