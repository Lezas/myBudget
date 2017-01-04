<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.24
 * Time: 21:41
 */

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Helper\DataFormatter;
use BudgetBundle\Repository\BudgetRepository;
use BudgetBundle\Response\AjaxBudgetResponse;
use MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class AjaxController
 * @package BudgetBundle\Controller
 * @Route("/api", condition="request.isXmlHttpRequest()")
 */
class AjaxController extends Controller
{
    /**
     * @param Expenses $expense
     * @param Request $request
     * @return array|JsonResponse
     * @Route("/new-expense/{expense}", name="ajax_new_expense")
     */
    public function NewExpenseAction(Expenses $expense = null, Request $request)
    {
        if ($expense === null) {
            $expense = new Expenses();
        }

        $action = $this->generateUrl('ajax_new_expense');

        if ($expense->getId() != null) {
            $action .= '/' . $expense->getId();
        }

        return $this->NewBudgetAction($request, $expense, $action);
    }

    /**
     * @param Income $income
     * @param Request $request
     * @return array|JsonResponse
     * @Route("/new-income/{income}", name="ajax_new_income")
     */
    public function NewIncomeAction(Income $income = null, Request $request)
    {
        if ($income === null) {
            $income = new Income();
        }

        $action = $this->generateUrl('ajax_new_income');
        if ($income->getId() != null) {
            $action .= '/' . $income->getId();
        }

        return $this->NewBudgetAction($request, $income, $action);
    }

    /**
     * @param Request $request
     * @param Budget $budget
     * @param string $action
     * @return JsonResponse
     */
    public function NewBudgetAction(Request $request, Budget $budget, $action)
    {
        $user = $this->getUser();

        $form = $this->get('budget.entity.form')->createBudgetForm($budget, $action, $user);

        $ajaxBudgetResponse = new AjaxBudgetResponse();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $budget->setUser($user);

            $em = $this->getDoctrine()->getManager();

            $em->persist($budget);
            $em->flush();

            $ajaxBudgetResponse->setDataToValid();
            $ajaxBudgetResponse->setResponseToSuccessful();

            return new JsonResponse($ajaxBudgetResponse->getResponse());
        }

        $ajaxBudgetResponse->setDataToInvalid();
        $ajaxBudgetResponse->setResponseToSuccessful();
        $ajaxBudgetResponse->setRenderedForm($this->render('BudgetBundle:Default:budgetForm.html.twig', [
            'form' => $form->createView(),
        ])->getContent()
        );

        return new JsonResponse($ajaxBudgetResponse->getResponse());
    }

    /**
     * @Route("/delete-income/{income}", name="ajax_delete_income")
     * @Security("user.getId() == income.getUser().getId()")
     * @param Income $income
     * @return JsonResponse
     */
    public function DeleteIncomeAction(Income $income = null)
    {
        if ($income === null) {
            throw $this->createNotFoundException();
        }

        $response = $this->deleteBudget($income);

        return new JsonResponse($response);
    }

    /**
     * @Route("/delete-expense/{expense}", name="ajax_delete_expense")
     * @Security("user.getId() == expense.getUser().getId()")
     * @param Expenses $expense
     * @return JsonResponse
     */
    public function DeleteExpenseAction(Expenses $expense = null)
    {
        if ($expense === null) {
            throw $this->createNotFoundException();
        }

        $response = $this->deleteBudget($expense);

        return new JsonResponse($response);
    }

    /**
     * @param Budget $budget
     * @return mixed
     */
    private function deleteBudget(Budget $budget)
    {
        $response = [];
        $response['success'] = false;

        $em = $this->getDoctrine()->getManager();
        $em->remove($budget);
        $em->flush();

        $response['success'] = true;

        return $response;
    }

    /**
     * @Route("/expenses-date-range", name="ajax_expense_by_date_range")
     * @return JsonResponse
     */
    public function GetExpenseByDateRangeAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Expenses');
        $return = $this->GetBudgetByDateRange($repository);

        return JsonResponse::create($return);
    }

    /**
     * @Route("/income-date-range", name="ajax_income_by_date_range")
     * @return JsonResponse
     */
    public function GetIncomeByDateRangeAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Income');
        $return = $this->GetBudgetByDateRange($repository);

        return JsonResponse::create($return);
    }

    /**
     * @param BudgetRepository $repository
     * @return array
     */
    private function GetBudgetByDateRange(BudgetRepository $repository)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $requestData = $this->get('budget.request.budgetbydaterange');

        $date_from = $requestData->getDateFrom();
        $date_to = $requestData->getDateTo();

        $budget = $repository->getByDateRange($user, $date_from, $date_to);

        $filtered_income = DataFormatter::groupByDay($budget);

        return $filtered_income;
    }

    /**
     * @Route("/income-list", name="ajax_income_list_by_date_range")
     * @return JsonResponse
     */
    public function GetIncomeListByDateRangeAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $response = [];

        $requestData = $this->get('budget.request.budgetbydaterange');

        $date_from = $requestData->getDateFrom();
        $date_to = $requestData->getDateTo();

        $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);

        $total = $this->get('budget.money.counter')->countBudget($income);

        $response['list'] = $this->render('BudgetBundle:Default:IncomeList.html.twig', [
            'list' => $income,
            'name' => 'income',
        ])->getContent();
        $response['date_from'] = $date_from->format('Y-m-d');
        $response['date_to'] = $date_to->format('Y-m-d');
        $response['total'] = $total;

        return new JsonResponse($response);
    }

    /**
     * @Route("/expense-list", name="ajax_expense_list_by_date_range")
     * @return JsonResponse
     */
    public function GetExpenseListByDateRangeAction()
    {
        $response = [];
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $requestData = $this->get('budget.request.budgetbydaterange');

        $date_from = $requestData->getDateFrom();
        $date_to = $requestData->getDateTo();

        $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

        $total = $this->get('budget.money.counter')->countBudget($expense);

        $response['list'] = $this->render('BudgetBundle:Default:ExpenseList.html.twig', [
            'list' => $expense,
            'name' => 'expense',
        ])->getContent();
        $response['date_from'] = $date_from->format('Y-m-d');
        $response['date_to'] = $date_to->format('Y-m-d');
        $response['total'] = $total;

        return new JsonResponse($response);
    }

    /**
     * @Route("/expenses", name="ajax_expense")
     * @return JsonResponse
     */
    public function GetExpenseAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $data = $this->get('budget.repository.budget')->getMonthBudget(null, $user);

        $expense = $data['expenses'];

        $filtered_expense = DataFormatter::groupByDay($expense);

        return JsonResponse::create($filtered_expense);
    }

    /**
     * @Route("/budget/chart-data", name="ajax_budget_chart_data")
     * @return JsonResponse
     */
    public function GetBudgetForChart()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $requestData = $this->get('budget.request.budgetbydaterange');

        $date_from = $requestData->getDateFrom();
        $date_to = $requestData->getDateTo();

        $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);
        $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);

        $filteredExpense = DataFormatter::groupByDay($expense);
        $filteredIncome = DataFormatter::groupByDay($income);

        $data = DataFormatter::connectData($filteredExpense, $filteredIncome);

        return JsonResponse::create($data);
    }


}