<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use Doctrine\DBAL\Types\StringType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="dashboard")
     */
    public function indexAction()
    {
        $budget_array = $this->getMonthBudget();

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach($budget_array['income'] as $array){
            $totalIncome += (float)$array['money'];
        }

        foreach($budget_array['expenses'] as $array){
            $totalExpenses += (float)$array['money'];
        }


        dump($budget_array['expenses']);


        return $this->render('BudgetBundle:Default:index.html.twig',[
            'total_expense' =>$totalExpenses,
            'total_income' => $totalIncome,
            'income' => $budget_array['income'],
            'expenses' => $budget_array['expenses'],
        ]);
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
            $income = $data['income'];

            $filtered_expense = $this->groupByDay($expense);
            return JsonResponse::create($filtered_expense);
        }

        throw new NotFoundHttpException("Page not found");

    }

    /**
     * @param Request $request
     * @Route("/api/expenses-date-range", name="ajax_expense_by_date_range")
     * @return Response
     */
    public function ajaxGetExpenseByDateRangeAction(Request $request)
    {
        if($this->isLogged()) {

            dump($request->get('date_from'));

            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $expense = $this->getExpensesByDateRange($date_from, $date_to);

            $filtered_expense = $this->groupByDay($expense);

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

            $cl = new DateTime();

            $this->groupByDay($budget_array['expenses']);


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
     * @return array\ - returns array of income and expenses.    ['income' => ['2015-02-02 00:00' => 58.85,],
     *                                                          'expenses' => ['2015-02-02 00:00' => 58.85,]]
     */
    public function getBudgetByDateRange($date_from, $date_to)
    {
        $income = $this->getIncomeByDateRange($date_from, $date_to);

        $expense = $this->getExpensesByDateRange($date_from, $date_to);

        $budget = array();

        $budget['income'] = $income;

        $budget['expenses'] = $expense;

        return $budget;
    }

    /**
     * @param $date_from
     * @param $date_to
     * @return mixed  ['2015-02-02 00:00' => 58.85,]
     */
    public function getIncomeByDateRange($date_from, $date_to)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            ORDER BY p.dateTime'
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $income = $query->getArrayResult();

        return $income;
    }

    /**
     * @param $date_from
     * @param $date_to
     * @return mixed
     */
    public function getExpensesByDateRange($date_from, $date_to)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
            FROM BudgetBundle:Expenses p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            ORDER BY p.dateTime
            '
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $expenses = $query->getArrayResult();


        return $expenses;
    }

    /**
     * @param $data
     * @return array
     */
    public function groupByDay($data)
    {
        for($i = 0; $i < count($data); $i++){
            $row = $data[$i];
            if(isset($row['deleted']) != true)
            for($o = $i+1; $o < count($data); $o++){
                $second_row = $data[$o];

                if(isset($second_row['deleted']) != true) {
                    if ($row['dateTime']->format('Y-m-d') === $second_row['dateTime']->format('Y-m-d')) {
                        $data[$i]['money'] += $data[$o]['money'];
                        $data[$o]['deleted'] = true;
                    }
                }

            }
        }

        $return_data = [];

        foreach($data as $key => $value){
            if(!isset($value['deleted'])){
                $return_data[]= [$data[$key]['dateTime']->format('Y-m-d'), (float)$data[$key]['money']];
            }
        }

        return $return_data;
    }
}
