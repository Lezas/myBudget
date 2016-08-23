<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\DataFormatter;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Date;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="dashboard")
     */
    public function indexAction()
    {

        if($this->isLogged()){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $repository = $this->get('budget.repository.budget');
            $budget_array = $repository->getMonthBudget(null, $user);

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

            return $this->render('BudgetBundle:Default:index.html.twig',[
                'total_expense' =>$totalExpenses,
                'total_income' => $totalIncome,
                'income' => $budget_array['income'],
                'expenses' => $budget_array['expenses'],
                'month_first_day' => date('Y-m-01', $date->getTimestamp()),
                'month_last_day' => date('Y-m-t', $date->getTimestamp()),
            ]);
        }

        return $this->redirectToRoute('fos_user_security_login');
    }

    /**
     * @Route("/income/new", name="new_income")
     */
    public function newIncomeAction(Request $request)
    {
        if (!$this->isLogged()) {

            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $user = $this->getUser();
            $income = new Income();

            $form = $this->createForm(IncomeType::class, $income, ['user' => $user]);

            $form->handleRequest($request);

            if($form->isValid()){
                $user = $this->getUser();
                $raw_data = $form->getData();

                $income->setDateTime($raw_data->getDateTime()['date_time']);
                $income->setUser($user);

                $em = $this->getDoctrine()->getManager();

                $em->persist($income);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Your income has been saved!'
                );
                return $this->redirectToRoute('dashboard');
            }

            return $this->render('BudgetBundle:Default:newIncome.html.twig',[
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @Route("/expense/new", name="new_expense")
     * @return Response
     */
    public function newExpenseAction(Request $request)
    {
        if (!$this->isLogged()) {

            return $this->redirectToRoute('fos_user_security_login');
        } else {

            $user = $this->getUser();
            $expenses = new Expenses();
            $form = $this->createForm(ExpenseType::class, $expenses, ['user' => $user]);

            $form->handleRequest($request);

            if($form->isValid()){
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

                return $this->redirectToRoute('dashboard');
            }

            return $this->render('BudgetBundle:Default:newExpense.html.twig',[
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @Route("/test", name="new_expense")
     * @return Response
     */
    public function testAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($request->query->get('date_from') != "" || $request->query->get('date_to') != "") {
            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));
        } else {
            $date = new \DateTime('now');

            //how to get first and last day of the month
            $date_from = new \DateTime(date('Y-m-01', $date->getTimestamp()));
            $date_to = new \DateTime(date('Y-m-t', $date->getTimestamp()));
        }

        $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);
        $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);

        $filteredExpense = DataFormatter::groupByDay($expense);
        $filteredIncome = DataFormatter::groupByDay($income);

        dump($filteredExpense);
        dump($filteredIncome);
        exit;

        $data = DataFormatter::connectData($filteredExpense, $filteredIncome);


    }

    /**
     * @Route("/report", name="reports")
     */
    public function reportsAction()
    {

        if($this->isLogged()){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $repository = $this->get('budget.repository.budget');
            $budget_array = $repository->getMonthBudget(null, $user);

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

            /*$o = 1;

$str = '';
            for ($o = 1;$o <= 60; $o++) {
                $str .= $o;
                $str .= ':00, ';
            }

            var_dump($str);*/
            return $this->render('BudgetBundle:Default:reports.html.twig',[
                'total_expense' =>$totalExpenses,
                'total_income' => $totalIncome,
                'income' => $budget_array['income'],
                'expenses' => $budget_array['expenses'],
                'month_first_day' => date('Y-m-01', $date->getTimestamp()),
                'month_last_day' => date('Y-m-t', $date->getTimestamp()),
            ]);
        }

        return $this->redirectToRoute('fos_user_security_login');
    }


    /**
     * @return bool - True if user is logged in, false if not.
     */
    private function isLogged()
    {
        $securityContext = $this->container->get('security.authorization_checker');

        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            return false;
        } else {

            return true;
        }
    }

}
