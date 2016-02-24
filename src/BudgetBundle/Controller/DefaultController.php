<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\DataFormatter;
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
            $budget_array = $this->getMonthBudget();

            $totalIncome = 0;
            $totalExpenses = 0;

            foreach($budget_array['income'] as $array){
                $totalIncome += (float)$array['money'];
            }

            foreach($budget_array['expenses'] as $array){
                $totalExpenses += (float)$array['money'];
            }

            return $this->render('BudgetBundle:Default:index.html.twig',[
                'total_expense' =>$totalExpenses,
                'total_income' => $totalIncome,
                'income' => $budget_array['income'],
                'expenses' => $budget_array['expenses'],
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

            $income = new Income();
            $form = $this->createForm(IncomeType::class, $income);

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
            $expenses = new Expenses();
            $form = $this->createForm(ExpenseType::class, $expenses);

            $form->handleRequest($request);

            if($form->isValid()){
                $user = $this->getUser();
                $raw_data = $form->getData();


                $expenses->setDateTime($raw_data->getDateTime()['date_time']);
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
     * @Route("/api/expenses", name="ajax_expense")
     */
    public function ajaxGetExpenseAction()
    {
        if($this->isLogged()) {
            $data = $this->getMonthBudget();
            $expense = $data['expenses'];

            $filtered_expense = DataFormatter::groupByDay($expense);
            return JsonResponse::create($filtered_expense);
        }

        throw new NotFoundHttpException("Page not found");
    }


    /**
     *
     * @param Date $date - date object MUST be valid datetime object or string of format YYYY-MM-DD
     * @return array
     */
    public function getMonthBudget(Date $date = null)
    {
        if (!$this->isLogged()) {

            return $this->redirectToRoute('fos_user_security_login');
        } else {

            //if $dat is null, return this month budget
            if($date === null){
                $date = new \DateTime('now');
            }

            //how to get first and last day of the month
            $month_first_day = date('Y-m-01', $date->getTimestamp());
            $month_last_day = date('Y-m-t', $date->getTimestamp());

            $budget_array = $this->getBudgetByDateRange($month_first_day, $month_last_day);

            return $budget_array;
        }
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

    /**
     * @param $date_from - can be timestamp, or date, or string. Auto convert is enabled
     * @param $date_to
     * @return array - returns array of income and expenses.    ['income' => ['2015-02-02 00:00' => 58.85,],
     *                                                          'expenses' => ['2015-02-02 00:00' => 58.85,]]
     */
    public function getBudgetByDateRange($date_from, $date_to)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);
        $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

        $budget = array();

        $budget['income'] = $income;
        $budget['expenses'] = $expense;

        return $budget;
    }

}
