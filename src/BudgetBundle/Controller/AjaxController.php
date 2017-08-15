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
use BudgetBundle\Helper\DateTime\DateTimeHelper;
use BudgetBundle\Repository\BudgetRepository;
use BudgetBundle\Response\AjaxBudgetResponse;
use CategoryBundle\Entity\Category;
use DateTime;
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
     * @param Request $request
     * @return array|JsonResponse
     * @Route("/income-data", name="ajax_get_income_data")
     */
    public function getIncomeDataAction(Request $request)
    {
        $user = $this->getUser();

        $categoryRepository = $this->getDoctrine()->getManager()->getRepository('CategoryBundle:Category');
        $incomeRepository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Income');

        list($month_first_day, $month_last_day) = $this->getDataRange($request);

        /** @var DateTime $month_first_day */
        $request->query->set('date_from', $month_first_day->format('Y-m-d'));
        $request->query->set('date_to', $month_last_day->format('Y-m-d'));

        $incomeCategories = $categoryRepository->findBy(['user' => $user, 'type' => 'income']);

        $incomeData = [];
        $totalIncome = 0;
        foreach ($incomeCategories as $incomeCategory) {
            $categoryIncome = $incomeRepository->getByDateRangeAndCategories($user, $month_first_day->format('Y-m-d H:i'), $month_last_day->format('Y-m-d H:i'), [$incomeCategory->getId()]);
            $total = $this->get('budget.money.counter')->countBudget($categoryIncome);
            if ($total > 0) {
                $totalIncome += $total;
                $incomeData[] = [
                    'category' => $incomeCategory,
                    'categoryBudget' => $categoryIncome,
                    'total' => $total
                ];
            }
        }

        $view = $this->render('BudgetBundle:Default/Budget:budgetList.html.twig', [
            'budgetData' => $incomeData,
        ])->getContent();

        return new JsonResponse([
            'success' => true,
            'view' => $view,
            'total' => $totalIncome,
            ]);
    }

    /**
     * @param Request $request
     * @return array|JsonResponse
     * @Route("/expense-data", name="ajax_get_expense_data")
     */
    public function getExpenseDataAction(Request $request)
    {
        $user = $this->getUser();

        $categoryRepository = $this->getDoctrine()->getManager()->getRepository('CategoryBundle:Category');
        $expenseRepository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Expenses');

        list($month_first_day, $month_last_day) = $this->getDataRange($request);

        /** @var DateTime $month_first_day */
        $request->query->set('date_from', $month_first_day->format('Y-m-d'));
        $request->query->set('date_to', $month_last_day->format('Y-m-d'));

        $expenseCategories = $categoryRepository->findBy(['user' => $user, 'type' => 'expense']);

        $expenseData = [];
        $chartData = [];
        $totalExpense = 0;
        foreach ($expenseCategories as $expenseCategory) {
            $categoryExpense = $expenseRepository->getByDateRangeAndCategories($user,$month_first_day->format('Y-m-d H:i'),$month_last_day->format('Y-m-d H:i'),[$expenseCategory->getId()]    );
            $total =  $this->get('budget.money.counter')->countBudget($categoryExpense);
            if ($total > 0) {
                $chartData[] = ['label' => $expenseCategory->getName(), 'value' => $total];
                $totalExpense += $total;
                $expenseData[] = [
                    'category' => $expenseCategory,
                    'categoryBudget' => $categoryExpense,
                    'total' => $total
                ];
            }
        }

        $expensesWithNoCat = $expenseRepository->getByDateRangeWithoutCategories($user,$month_first_day,$month_last_day);
        $NoCat = new Category();
        $NoCat->setName("Without Category");
        $total =  $this->get('budget.money.counter')->countBudget($expensesWithNoCat);
        if ($total > 0) {
            $chartData[] = ['label' => "Without Category", 'value' => $total];
            $totalExpense += $total;
            $expenseData[] = [
                'category' => $NoCat,
                'categoryBudget' => $expensesWithNoCat,
                'total' => $total
            ];
        }

        $view = $this->render('BudgetBundle:Default/Budget:budgetList.html.twig', [
            'budgetData' => $expenseData,
        ])->getContent();

        return new JsonResponse([
            'success' => true,
            'view' => $view,
            'total' => $totalExpense,
            'chartData' => $chartData,
        ]);
    }

    /**
     * @param $request
     * @return array
     */
    protected function getDataRange(Request $request)
    {
        $date = new \DateTime('now');
        $dateTimeHelper = new DateTimeHelper();

        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $dateFrom = $dateFrom != null ? new DateTime($dateFrom) : $dateTimeHelper->getFirstDayOfMonth($date);
        $dateTo = $dateTo != null ? new DateTime($dateTo) : $dateTimeHelper->getLastDayOfMonth($date);

        return [
            $dateFrom,
            $dateTo
        ];
    }

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