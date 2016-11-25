<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\BudgetMoneyCounter;
use BudgetBundle\Helper\DataFormatter;
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

        foreach($budget_array['income'] as $array){
            /** @var $array Income */
            $totalIncome += (float)$array->getMoney();
        }

        foreach($budget_array['expenses'] as $array){
            /** @var $array Expenses */
            $totalExpenses += (float)$array->getMoney();
        }

        $date = new \DateTime('now');

        return $this->render('BudgetBundle:Default:reports.html.twig',[
            'income_categories' => $incomeCategories,
            'expense_categories' => $expenseCategories,
            'total_expense' =>$totalExpenses,
            'total_income' => $totalIncome,
            'income' => $budget_array['income'],
            'expenses' => $budget_array['expenses'],
            'month_first_day' => date('Y-m-01', $date->getTimestamp()),
            'month_last_day' => date('Y-m-t', $date->getTimestamp()),
        ]);

    }

    /**
     * @Route("/report/get/income", name="ajax_report_get_income")
     */
    public function ajaxGetIncomeListAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $dateFrom = $request->query->get('date_from');
            $dateTo = $request->query->get('date_to');
            $Ids = $request->query->get('ids');

            if ($dateFrom == "Lifetime" && $dateTo == "Lifetime"){
                return $this->getLifetimeIncomeListAction($request);
            }
            $IdsArrayCollection = new ArrayCollection();
            $result = [];
            $sum = 0;

            if ($Ids != null) {
                $incomes = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRangeAndCategories($user, $dateFrom, $dateTo, $Ids);
                $IdsArrayCollection = new ArrayCollection($Ids);
            }else {
                $incomes = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $dateFrom, $dateTo);
            }

            $Incomes = new ArrayCollection($incomes);
            $data = $this->createDataFromBudget($Incomes);
            $result = array_merge($result,$data->toArray());
            $sum += BudgetMoneyCounter::countBudget($Incomes);


            if ($IdsArrayCollection->contains("NULL")) {
                $additionalIncomes = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRangeWithoutCategories($user, $dateFrom, $dateTo);
                $additionalIncome = new ArrayCollection($additionalIncomes);

                $data = $this->createDataFromBudget($additionalIncome);
                $result = array_merge($result,$data->toArray());
                $sum += BudgetMoneyCounter::countBudget($additionalIncome);
            }

            $return = [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'data' => $result,
                'sum' => $sum
            ];
            return JsonResponse::create($return);
        }

        throw new NotFoundHttpException("Page not found");
    }

    public function getLifetimeIncomeListAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $Ids = $request->query->get('ids');

            $result = [];
            $sum =0;
            $IdsArrayCollection = new ArrayCollection();

            if ($Ids != null) {
                $Incomes = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByCategories($user, $Ids);
                $IdsArrayCollection = new ArrayCollection($Ids);
            } else {
                $Incomes = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getWithCategories($user);
                $Incomes2 = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getWithoutCategories($user);

                $Incomes = array_merge($Incomes,$Incomes2);
            }

            $Incomes = new ArrayCollection($Incomes);
            $data = $this->createDataFromBudget($Incomes);

            $result = array_merge($result,$data->toArray());
            $sum += BudgetMoneyCounter::countBudget($Incomes);

            if ($IdsArrayCollection->contains("NULL")) {
                $additionalIncome = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getWithoutCategories($user);
                $additionalIncome = new ArrayCollection($additionalIncome);

                $data = $this->createDataFromBudget($additionalIncome);
                $result = array_merge($result,$data->toArray());
                $sum += BudgetMoneyCounter::countBudget($additionalIncome);
            }

            $return = [
                'dateFrom' => "lifetime",
                'dateTo' => "lifetime",
                'data' => $result,
                'sum' => $sum
            ];
            return JsonResponse::create($return);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @Route("/report/get/expense", name="ajax_report_get_expense")
     */
    public function ajaxGetExpenseListAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $dateFrom = $request->query->get('date_from');
            $dateTo = $request->query->get('date_to');
            $Ids = $request->query->get('ids');

            $result = [];
            $sum =0;
            $IdsArrayCollection = new ArrayCollection();

            if ($dateFrom == "Lifetime" && $dateTo == "Lifetime"){
                return $this->getLifetimeExpenseListAction($request);
            }

            if ($Ids != null) {
                $expenses = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRangeAndCategories($user, $dateFrom, $dateTo, $Ids);
                $IdsArrayCollection = new ArrayCollection($Ids);
            } else {
                $expenses = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $dateFrom, $dateTo);
            }

            $expenses = new ArrayCollection($expenses);
            $data = $this->createDataFromBudget($expenses);
            $result = array_merge($result,$data->toArray());
            $sum += BudgetMoneyCounter::countBudget($expenses);

            if ($IdsArrayCollection->contains("NULL")) {
                $additionalExpenses = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRangeWithoutCategories($user, $dateFrom, $dateTo);
                $expenses = new ArrayCollection($additionalExpenses);

                $data = $this->createDataFromBudget($expenses);
                $result = array_merge($result,$data->toArray());
                $sum += BudgetMoneyCounter::countBudget($expenses);
            }

            $return = [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'data' => $result,
                'sum' => $sum
            ];
            return JsonResponse::create($return);
        }

        throw new NotFoundHttpException("Page not found");
    }

    public function getLifetimeExpenseListAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $Ids = $request->query->get('ids');

            $result = [];
            $sum =0;
            $IdsArrayCollection = new ArrayCollection();
            if ($Ids != null) {
                $expenses = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByCategories($user, $Ids);
                $IdsArrayCollection = new ArrayCollection($Ids);
            } else {
                $expenses = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getWithCategories($user);
                $expenses2 = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getWithoutCategories($user);

                $expenses = array_merge($expenses,$expenses2);
            }

            $expenses = new ArrayCollection($expenses);
            $data = $this->createDataFromBudget($expenses);

            $result = array_merge($result,$data->toArray());
            $sum += BudgetMoneyCounter::countBudget($expenses);

            if ($IdsArrayCollection->contains("NULL")) {
                $additionalExpenses = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getWithoutCategories($user);
                $expenses = new ArrayCollection($additionalExpenses);

                $data = $this->createDataFromBudget($expenses);
                $result = array_merge($result,$data->toArray());
                $sum += BudgetMoneyCounter::countBudget($expenses);
            }

            $return = [
                'dateFrom' => "lifetime",
                'dateTo' => "lifetime",
                'data' => $result,
                'sum' => $sum
            ];
            return JsonResponse::create($return);
        }

        throw new NotFoundHttpException("Page not found");
    }


    /**
     * @param $budgets ArrayCollection of Budget
     * @return ArrayCollection
     */
    public function createDataFromBudget($budgets)
    {
        $result = new ArrayCollection();
        foreach ($budgets as $budget) {
            /** @var Budget $budget */

            $categoryName = "-";
            if ($budget->getCategory() != null) {
                $categoryName = htmlspecialchars($budget->getCategory()->getName());
            }

            $data = [
                $budget->getDateTime()->format('Y-m-d H:i:s'),
                htmlspecialchars($budget->getName()),
                $categoryName,
                $budget->getMoney()
            ];

            $result->add($data);
        }

        return $result;
    }

}
