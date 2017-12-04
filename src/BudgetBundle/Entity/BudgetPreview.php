<?php

namespace BudgetBundle\Entity;

use BudgetBundle\Helper\BudgetMoneyCounter;
use BudgetBundle\Model\DateRange;
use BudgetBundle\Repository\BudgetCategoryRepository;
use BudgetBundle\Repository\BudgetRepository;
use CategoryBundle\Entity\Category;

/**
 * Class BudgetPreview
 */
class BudgetPreview
{
    /** @var array */
    protected $budgetData = [];

    /** @var BudgetCategoryRepository */
    protected $budgetCategoryRepository;

    /** @var BudgetRepository */
    protected $budgetRepository;

    /** @var BudgetMoneyCounter */
    protected $budgetCounter;

    /**
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
     * @param DateRange $dateRange
     *
     * @return array
     */
    public function calculateBudget($user, DateRange $dateRange)
    {
        $budgetCategories = $this->budgetCategoryRepository->getAllUserBudgetCategories($user);

        foreach ($budgetCategories as $category) {
            $categoryIncome = $this->budgetRepository->getByDateRangeAndCategories($user, $dateRange, [$category->getId()]);
            $total = $this->budgetCounter->countBudget($categoryIncome);
            if ($total > 0) {
                $this->addBudget($category, $categoryIncome, $total);
            }
        }

        $budgetWithNoCat = $this->budgetRepository->getByDateRangeWithoutCategories($user, $dateRange);
        $total = $this->budgetCounter->countBudget($budgetWithNoCat);
        $NoCat = new Category();
        $NoCat->setName('Without Category');
        if ($total > 0) {
            $this->addBudget($NoCat, $budgetWithNoCat, $total);
        }

        return $this->getData();
    }
}