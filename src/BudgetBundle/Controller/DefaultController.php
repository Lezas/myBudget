<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\DateRangeType;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\DataFormatter;
use BudgetBundle\Helper\DateTime\DateTimeHelper;
use CategoryBundle\Entity\Category;
use DateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use MainBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;


/**
 * Class DefaultController
 * @package BudgetBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/oldDash", name="dashboard")
     */
    public function indexAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository = $this->get('budget.repository.budget');
        $budget_array = $repository->getMonthBudget(null, $user);

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($budget_array['income'] as $array) {
            /** @var $array Income */
            $totalIncome += (float)$array->getMoney();
        }

        foreach ($budget_array['expenses'] as $array) {
            /** @var $array Expenses */
            $totalExpenses += (float)$array->getMoney();
        }

        $date = new \DateTime('now');
        $dateTimeHelper = $this->get('helper.datetime');

        $firstDay = $dateTimeHelper->getFirstDayOfMonth($date);
        $lastDay = $dateTimeHelper->getLastDayOfMonth($date);

        return $this->render('BudgetBundle:Default:index.html.twig', [
            'total_expense' => $totalExpenses,
            'total_income' => $totalIncome,
            'income' => $budget_array['income'],
            'expenses' => $budget_array['expenses'],
            'month_first_day' => $firstDay->format('Y-m-d H:i'),
            'month_last_day' => $lastDay->format('Y-m-d H:i'),
        ]);
    }

    /**
     * @Route("/", name="new_dashboard")
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function newDashboardAction(Request $request)
    {
        $user = $this->getUser();

        $categoryRepository = $this->getDoctrine()->getManager()->getRepository('CategoryBundle:Category');
        $incomeRepository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Income');
        $expenseRepository = $this->getDoctrine()->getManager()->getRepository('BudgetBundle:Expenses');

        list($month_first_day, $month_last_day) = $this->getDataRange($request);

        /** @var DateTime $month_first_day */
        $request->query->set('date_from', $month_first_day->format('Y-m-d'));
        $request->query->set('date_to', $month_last_day->format('Y-m-d'));

        $incomeCategories = $categoryRepository->findBy(['user' => $user, 'type' => 'income']);
        $expenseCategories = $categoryRepository->findBy(['user' => $user, 'type' => 'expense']);

        $incomeData = [];
        $totalIncome = 0;
        foreach ($incomeCategories as $incomeCategory) {
            $categoryIncome = $incomeRepository->getByDateRangeAndCategories($user,$month_first_day->format('Y-m-d H:i'),$month_last_day->format('Y-m-d H:i'),[$incomeCategory->getId()]    );
            $total =  $this->get('budget.money.counter')->countBudget($categoryIncome);
            if ($total > 0) {
                $totalIncome += $total;
                $incomeData[] = [
                    'category' => $incomeCategory,
                    'categoryBudget' => $categoryIncome,
                    'total' => $total
                ];
            }
        }

        $expenseData = [];
        $chartData = [];
        $totalExpense = 0;
        foreach ($expenseCategories as $expenseCategory) {
            $categoryExpense = $expenseRepository->getByDateRangeAndCategories($user,$month_first_day->format('Y-m-d H:i'),$month_last_day->format('Y-m-d H:i'),[$expenseCategory->getId()]    );
            $total =  $this->get('budget.money.counter')->countBudget($categoryExpense);
            if ($total > 0) {
                $chartData[] = ['label' => $expenseCategory->getName(), 'value' => round($total)];
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
            $chartData[] = ['label' => "Without Category", 'value' => round($total)];
            $totalExpense += $total;
            $expenseData[] = [
                'category' => $NoCat,
                'categoryBudget' => $expensesWithNoCat,
                'total' => round($total,2)
            ];
        }

        $dateRangeForm = $this->createForm(DateRangeType::class);
        $dateRangeForm->get('dateFrom')->setData($month_first_day);
        $dateRangeForm->get('dateTo')->setData($month_last_day);


        return $this->render("@Budget/Default/newDashboard.html.twig", [
            'chartData' => json_encode($chartData),
            'incomeData' => $incomeData,
            'expenseData' => $expenseData,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'firstDay' => $month_first_day,
            'lastDay' => $month_last_day,
            'incomeForm' => $this->get('budget.entity.form')->createBudgetForm(new Income(),$this->generateUrl('ajax_new_income'), $user)->createView(),
            'expenseForm' => $this->get('budget.entity.form')->createBudgetForm(new Expenses(),$this->generateUrl('ajax_new_expense'), $user)->createView(),
            'dateRangeForm' => $dateRangeForm->createView(),
        ]);
    }

    /**
     * @Route("/income/new", name="new_income")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newIncomeAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $income = new Income();

        $form = $this->get('budget.entity.form')->createBudgetForm($income,$this->generateUrl('new_income'), $user);

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
     * @Route("/expense/new", name="new_expense")
     * @return Response
     */
    public function newExpenseAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $expenses = new Expenses();
        $form = $this->get('budget.entity.form')->createBudgetForm($expenses,$this->generateUrl('new_income'), $user);
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

}
