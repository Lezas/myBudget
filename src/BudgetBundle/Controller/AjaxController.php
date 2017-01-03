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
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
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
        $ajaxBudgetResponse = new AjaxBudgetResponse();

        if ($expense === null) {
            $expense = new Expenses();
        }

        /** @var User $user */
        $user = $this->getUser();
        $expenseCategories = $this->get('category.repository.service')->getAllUserExpenseCategories($user);

        $form = $this->createForm(ExpenseType::class, $expense, [
            'action' => $this->generateUrl('ajax_new_expense'),
            'attr' => ['class' => 'create_budget'],
            'method' => 'POST',
            'user' => $user,
            'categories' => $expenseCategories,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $raw_data = $form->getData();

            $expense->setDateTime($raw_data->getDateTime());
            $expense->setUser($user);

            $em = $this->getDoctrine()->getManager();

            $em->persist($expense);
            $em->flush();

            $ajaxBudgetResponse->setDataToValid();
            $ajaxBudgetResponse->setResponseToSuccessful();

            return new JsonResponse($ajaxBudgetResponse->getResponse());
        }

        $ajaxBudgetResponse->setDataToInvalid();
        $ajaxBudgetResponse->setResponseToSuccessful();
        $ajaxBudgetResponse->setRenderedForm($this->render('BudgetBundle:Default:expenseForm.html.twig', [
            'form' => $form->createView(),
        ])->getContent()
        );

        return new JsonResponse($ajaxBudgetResponse->getResponse());
    }

    /**
     * @Route("/update-expense/{expense}", name="ajax_update_expense")
     * @Security("user.getId() == expense.getUser().getId()")
     * @param Expenses $expense
     * @param Request $request
     * @return JsonResponse
     */
    public function UpdateExpense(Expenses $expense = null, Request $request)
    {
        $response = [];
        $response['success'] = false;

        if ($expense === null) {
            $response['cause'] = 'You must specify expense id';
            return new JsonResponse($response);
        } else {
            $user = $this->getUser();
            $expenseCategories = $this->get('category.repository.service')->getAllUserExpenseCategories($user);

            $form = $this->createForm(ExpenseType::class, $expense, array(
                'action' => $this->generateUrl('ajax_update_expense'),
                'attr' => array('class' => 'create_event_form', 'data-id' => $expense->getId()),
                'method' => 'POST',
                'user' => $user,
                'categories' => $expenseCategories,
            ));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $raw_data = $form->getData();

                $expense->setDateTime($raw_data->getDateTime());

                $em = $this->getDoctrine()->getManager();

                $em->persist($expense);
                $em->flush();

                $response['valid'] = true;
                $response['success'] = true;
                return new JsonResponse($response);
            }

            $response['valid'] = false;
            $response['success'] = true;
            $response['form'] = $this->render('BudgetBundle:Default:expenseForm.html.twig', [
                'form' => $form->createView(),
            ])->getContent();

            return new JsonResponse($response);

        }
    }

    /**
     * @param Request $request
     * @Route("/new-income", name="ajax_new_income")
     * @return array|JsonResponse
     */
    public function NewIncomeAction(Request $request)
    {
        $ajaxBudgetResponse = new AjaxBudgetResponse();
        /** @var User $user */
        $user = $this->getUser();
        $income = new Income();
        $incomeCategories = $this->get('category.repository.service')->getAllUserIncomeCategories($user);

        $form = $this->createForm(IncomeType::class, $income, array(
            'action' => $this->generateUrl('ajax_new_income'),
            'attr' => array('class' => 'create_budget'),
            'method' => 'POST',
            'user' => $user,
            'categories' => $incomeCategories,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $raw_data = $form->getData();

            $income->setDateTime($raw_data->getDateTime());
            $income->setUser($user);

            $em = $this->getDoctrine()->getManager();

            $em->persist($income);
            $em->flush();

            $ajaxBudgetResponse->setDataToValid();
            $ajaxBudgetResponse->setResponseToSuccessful();
            return new JsonResponse($ajaxBudgetResponse->getResponse());
        }

        $ajaxBudgetResponse->setDataToInvalid();
        $ajaxBudgetResponse->setResponseToSuccessful();
        $ajaxBudgetResponse->setRenderedForm($this->render('BudgetBundle:Default:ajaxIncomeForm.html.twig', [
            'form' => $form->createView(),
        ])->getContent());

        return new JsonResponse($ajaxBudgetResponse->getResponse());
    }



    /**
     * @Route("/update-income/{income}", name="ajax_update_income")
     * @Security("user.getId() == income.getUser().getId()")
     * @param Income $income
     * @param Request $request
     * @return JsonResponse
     */
    public function UpdateIncome(Income $income = null, Request $request)
    {
        $response = [];
        $response['success'] = false;

        if ($income === null) {
            $response['cause'] = 'You must specify income id';
            return new JsonResponse($response);
        } else {
            $user = $this->getUser();

            $incomeCategories = $this->get('category.repository.service')->getAllUserIncomeCategories($user);
            $form = $this->createForm(IncomeType::class, $income, [
                'action' => $this->generateUrl('ajax_update_income'),
                'attr' => ['class' => 'create_event_form', 'data-id' => $income->getId()],
                'method' => 'POST',
                'user' => $user,
                'categories' => $incomeCategories,
            ]);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $raw_data = $form->getData();

                $income->setDateTime($raw_data->getDateTime());

                $em = $this->getDoctrine()->getManager();

                $em->persist($income);
                $em->flush();

                $response['success'] = true;
                $response['valid'] = true;
                return new JsonResponse($response);
            }

            $response['valid'] = false;

            $response['success'] = true;
            $response['form'] = $this->render('BudgetBundle:Default:ajaxIncomeForm.html.twig', [
                'form' => $form->createView(),
            ])->getContent();

            return new JsonResponse($response);

        }
    }

    /**
     * @Route("/delete-income/{income}", name="ajax_delete_income")
     * @Security("user.getId() == income.getUser().getId()")
     * @param Income $income
     * @return JsonResponse
     */
    public function DeleteIncome(Income $income = null)
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
    public function DeleteExpense(Expenses $expense = null)
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
     * @param Request $request
     * @Route("/expenses-date-range", name="ajax_expense_by_date_range")
     * @return JsonResponse
     */
    public function GetExpenseByDateRangeAction(Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Expenses');
        $return = $this->GetBudgetByDateRange($request, $repository);

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     * @Route("/income-date-range", name="ajax_income_by_date_range")
     * @return JsonResponse
     */
    public function GetIncomeByDateRangeAction(Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Income');
        $return = $this->GetBudgetByDateRange($request, $repository);

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     * @param BudgetRepository $repository
     * @return array
     */
    private function GetBudgetByDateRange(Request $request, BudgetRepository $repository)
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
     * @param Request $request
     * @Route("/income-list", name="ajax_income_list_by_date_range")
     * @return JsonResponse
     */
    public function GetIncomeListByDateRangeAction(Request $request)
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
     * @param Request $request
     * @Route("/expense-list", name="ajax_expense_list_by_date_range")
     * @return JsonResponse
     */
    public function GetExpenseListByDateRangeAction(Request $request)
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
     * @param Request $request
     * @Route("/budget/chart-data", name="ajax_budget_chart_data")
     * @return JsonResponse
     */
    public function GetBudgetForChart(Request $request)
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