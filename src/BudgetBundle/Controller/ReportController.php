<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\BudgetMoneyCounter;
use BudgetBundle\Helper\DataFormatter;
use BudgetBundle\Repository\BudgetRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class ReportController
 * @package BudgetBundle\Controller
 */
class ReportController extends Controller
{

    /**
     * @Route("/report", name="reports")
     */
    public function reportsAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository = $this->get('budget.repository.budget');
        $oCategoryRepService = $this->get('category.repository.service');

        $budget_array = $repository->getMonthBudget(null, $user);

        $incomeCategories = $oCategoryRepService->getAllUserIncomeCategories($user);
        $expenseCategories = $oCategoryRepService->getAllUserExpenseCategories($user);

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($budget_array['income'] as $budget) {
            /** @var $budget Budget */
            $totalIncome += (float)$budget->getMoney();
        }

        foreach ($budget_array['expenses'] as $budget) {
            /** @var $budget Budget */
            $totalExpenses += (float)$budget->getMoney();
        }

        $date = new \DateTime('now');

        return $this->render('BudgetBundle:Default:reports.html.twig', [
            'income_categories' => $incomeCategories,
            'expense_categories' => $expenseCategories,
            'total_expense' => $totalExpenses,
            'total_income' => $totalIncome,
            'income' => $budget_array['income'],
            'expenses' => $budget_array['expenses'],
            'month_first_day' => date('Y-m-01', $date->getTimestamp()),
            'month_last_day' => date('Y-m-t', $date->getTimestamp()),
        ]);

    }

}
