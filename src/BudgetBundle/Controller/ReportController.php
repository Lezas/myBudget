<?php

namespace BudgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Class ReportController
 * @package BudgetBundle\Controller
 */
class ReportController extends Controller
{

    /**
     * @Route("/report", name="reports")
     * @Security("has_role('ROLE_USER')")
     */
    public function reportsAction()
    {
        $user = $this->getUser();
        $repository = $this->get('budget.repository.budget');
        $oCategoryRepService = $this->get('category.repository.service');
        $budgetUtility = $this->get('budget.utility');

        $budget_array = $repository->getMonthBudget(null, $user);

        $incomeCategories = $oCategoryRepService->getAllUserIncomeCategories($user);
        $expenseCategories = $oCategoryRepService->getAllUserExpenseCategories($user);

        $totalIncome = $budgetUtility->sumBudget($budget_array['income']);
        $totalExpenses = $budgetUtility->sumBudget($budget_array['expenses']);

        $date = new \DateTime('now');
        $dateTimeHelper = $this->get('helper.datetime');

        $firstDay = $dateTimeHelper->getFirstDayOfMonth($date);
        $lastDay = $dateTimeHelper->getLastDayOfMonth($date);

        return $this->render('BudgetBundle:Default:reports.html.twig', [
            'income_categories'  => $incomeCategories,
            'expense_categories' => $expenseCategories,
            'total_expense'      => $totalExpenses,
            'total_income'       => $totalIncome,
            'income'             => $budget_array['income'],
            'expenses'           => $budget_array['expenses'],
            'month_first_day'    => $firstDay,
            'month_last_day'     => $lastDay,
        ]);

    }

}
