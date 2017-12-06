<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\DonutChart;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\DateRangeType;
use CategoryBundle\Entity\Category;
use MainBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @Route("/oldDash", name="dashboard")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $repository = $this->get('budget.request.budgetbydaterange');
        $budget_array = $repository->getMonthBudget(null, $user);
        $budgetCounter = $this->get('budget.money.counter');

        $totalIncome = $budgetCounter->countBudget($budget_array['income']);
        $totalExpenses = $budgetCounter->countBudget($budget_array['expenses']);

        $date = new \DateTime('now');
        $dateTimeHelper = $this->get('helper.datetime');

        $firstDay = $dateTimeHelper->getFirstDayOfMonth($date);
        $lastDay = $dateTimeHelper->getLastDayOfMonth($date);

        return $this->render('BudgetBundle:Default:index.html.twig', [
            'total_expense'   => $totalExpenses,
            'total_income'    => $totalIncome,
            'income'          => $budget_array['income'],
            'expenses'        => $budget_array['expenses'],
            'month_first_day' => $firstDay->format('Y-m-d H:i'),
            'month_last_day'  => $lastDay->format('Y-m-d H:i'),
        ]);
    }

    /**
     * @Route("/", name="new_dashboard")
     *
     * @return Response
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function newDashboardAction()
    {
        $user = $this->getUser();
        $dateRange = $this->get('budget.request.budgetbydaterange')->getDateRange();

        $incomeBudgetPreview = $this->get('budget.income.preview');
        $incomeBudgetPreview->calculateBudget($user, $dateRange);

        $expenseBudgetPreview = $this->get('budget.expense.preview');
        $expenseBudgetPreview->calculateBudget($user, $dateRange);

        $donutChart = new DonutChart();
        foreach ($expenseBudgetPreview->getData() as $BudgetData) {
            /** @var Category $category */
            $category = $BudgetData['category'];
            $total = $BudgetData['total'];
            $donutChart->addData($category->getName(), round($total, 2));
        }

        $dateRangeForm = $this->createForm(DateRangeType::class);
        $dateRangeForm->get('dateFrom')->setData($dateRange->getDateFrom());
        $dateRangeForm->get('dateTo')->setData($dateRange->getDateTo());

        return $this->render("@Budget/Default/newDashboard.html.twig", [
            'chartData'     => json_encode($donutChart->generateChartData()),
            'incomeData'    => $incomeBudgetPreview->getData(),
            'totalIncome'   => $incomeBudgetPreview->getTotalMoneyCount(),
            'expenseData'   => $expenseBudgetPreview->getData(),
            'totalExpense'  => $expenseBudgetPreview->getTotalMoneyCount(),
            'firstDay'      => $dateRange->getDateFrom(),
            'lastDay'       => $dateRange->getDateTo(),
            'incomeForm'    => $this->get('budget.entity.form')->createBudgetForm(new Income(), $this->generateUrl('ajax_new_income'), $user)->createView(),
            'expenseForm'   => $this->get('budget.entity.form')->createBudgetForm(new Expenses(), $this->generateUrl('ajax_new_expense'), $user)->createView(),
            'dateRangeForm' => $dateRangeForm->createView(),
        ]);
    }

    /**
     * @Route("/income/new", name="new_income")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newIncomeAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $income = new Income();

        $form = $this->get('budget.entity.form')->createBudgetForm($income, $this->generateUrl('new_income'), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $raw_data = $form->getData();

            $income->setDateTime($raw_data->getDateTime());
            $income->setUser($user);

            $em = $this->getDoctrine()->getManager();

            $em->persist($income);
            $em->flush();
            $this->addFlash(
                'notice',
                'Your income has been saved!'
            );

            return $this->redirectToRoute('new_dashboard');
        }

        return $this->render('BudgetBundle:Default:newIncome.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @Route("/expense/new", name="new_expense")
     *
     * @return Response
     */
    public function newExpenseAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $expenses = new Expenses();
        $form = $this->get('budget.entity.form')->createBudgetForm($expenses, $this->generateUrl('new_income'), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $raw_data = $form->getData();

            $expenses->setDateTime($raw_data->getDateTime());
            $expenses->setUser($user);

            $em = $this->getDoctrine()->getManager();

            $em->persist($expenses);
            $em->flush();
            $this->addFlash(
                'notice',
                'Your expense has been saved!'
            );

            return $this->redirectToRoute('new_dashboard');
        }

        return $this->render('BudgetBundle:Default:newExpense.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
