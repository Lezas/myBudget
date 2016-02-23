<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
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

            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $expense = $this->getExpensesByDateRange($date_from, $date_to);

            $filtered_expense = $this->groupByDay($expense);

            return JsonResponse::create($filtered_expense);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/api/income-date-range", name="ajax_income_by_date_range")
     * @return Response
     */
    public function ajaxGetIncomeByDateRangeAction(Request $request)
    {
        if($this->isLogged()) {

            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $income = $this->getIncomeByDateRange($date_from, $date_to);

            $filtered_income = $this->groupByDay($income);

            return JsonResponse::create($filtered_income);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/api/new-expense", name="ajax_new_expense")
     * @return array|JsonResponse
     */
    public function ajaxNewExpenseAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $response['success'] = false;

            if (!$this->isLogged()) {
                $response['cause'] = 'User is not logged in';
                return new JsonResponse($response);
            } else {

                $expense = new Expenses();

                $form = $this->createForm(ExpenseType::class, $expense, array(
                    'action' => $this->generateUrl('ajax_new_expense'),
                    'attr' => array('class' => 'create_budget'),
                    'method' => 'POST',
                ));

                $form->handleRequest($request);

                if($form->isValid()){
                    $user = $this->getUser();
                    $raw_data = $form->getData();

                    $expense->setDateTime($raw_data->getDateTime());
                    $expense->setUser($user);

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($expense);
                    $em->flush();

                    $response['success'] = true;
                    return new JsonResponse($response);
                }

                $response['success'] = true;
                $response['form'] = $this->render('BudgetBundle:Default:expenseForm.html.twig', [
                    'form' => $form->createView(),
                ])->getContent();

                return new JsonResponse($response);

            }
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }
    }

    /**
     * @param Request $request
     * @Route("/api/new-income", name="ajax_new_income")
     * @return array|JsonResponse
     */
    public function ajaxNewIncomeAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $response['success'] = false;

            if (!$this->isLogged()) {
                $response['cause'] = 'User is not logged in';
                return new JsonResponse($response);
            } else {

                $income = new Income();

                $form = $this->createForm(IncomeType::class, $income, array(
                    'action' => $this->generateUrl('ajax_new_income'),
                    'attr' => array('class' => 'create_budget'),
                    'method' => 'POST',
                ));

                $form->handleRequest($request);

                if($form->isValid()){
                    $user = $this->getUser();
                    $raw_data = $form->getData();

                    $income->setDateTime($raw_data->getDateTime());
                    $income->setUser($user);

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($income);
                    $em->flush();

                    $response['success'] = true;
                    return new JsonResponse($response);
                }

                $response['success'] = true;
                $response['form'] = $this->render('BudgetBundle:Default:ajaxIncomeForm.html.twig', [
                    'form' => $form->createView(),
                ])->getContent();

                return new JsonResponse($response);

            }
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }
    }

    /**
     * @Route("/api/update-expense/{id}", name="ajax_update_expense")
     * @param null $id
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxUpdateExpense($id = null, Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $response['success'] = false;

            if (!$this->isLogged()) {
                $response['cause'] = 'User is not logged in';
                return new JsonResponse($response);
            } elseif($id === null){
                $response['cause'] = 'You must specify expense id';
                return new JsonResponse($response);
            } else {

                $em = $this->getDoctrine()->getEntityManager();
                $expense = $em->getRepository('BudgetBundle:Expenses')->find($id);
                $user = $this->getUser();
                $expenseUser = $expense->getUser();

                if($expense === null){
                    $response['cause'] = 'Cant found expense with that id';
                    return new JsonResponse($response);
                }
                if($expenseUser->getId() !== $user->getId()){
                    $response['cause'] = 'Cant found expense with that id';
                    return new JsonResponse($response);
                }

                $form = $this->createForm(ExpenseType::class, $expense, array(
                    'action' => $this->generateUrl('ajax_update_expense'),
                    'attr' => array('class' => 'create_event_form', 'data-id' => $expense->getId()),
                    'method' => 'POST',
                ));

                $form->handleRequest($request);

                if($form->isValid()){
                    $raw_data = $form->getData();

                    $expense->setDateTime($raw_data->getDateTime());

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($expense);
                    $em->flush();

                    $response['success'] = true;
                    return new JsonResponse($response);
                }

                $response['success'] = true;
                $response['form'] = $this->render('BudgetBundle:Default:expenseForm.html.twig', [
                    'form' => $form->createView(),
                ])->getContent();

                return new JsonResponse($response);

            }
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }

    }

    /**
     * @Route("/api/update-income/{id}", name="ajax_update_income")
     * @param null $id
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxUpdateIncome($id = null, Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $response['success'] = false;

            if (!$this->isLogged()) {
                $response['cause'] = 'User is not logged in';
                return new JsonResponse($response);
            } elseif($id === null){
                $response['cause'] = 'You must specify income id';
                return new JsonResponse($response);
            } else {

                $em = $this->getDoctrine()->getEntityManager();
                $income = $em->getRepository('BudgetBundle:Income')->find($id);
                $user = $this->getUser();
                $incomeUser = $income->getUser();

                if($income === null){
                    $response['cause'] = 'Cant found income with that id';
                    return new JsonResponse($response);
                }
                if($incomeUser->getId() !== $user->getId()){
                    $response['cause'] = 'Cant found income with that id';
                    return new JsonResponse($response);
                }

                $form = $this->createForm(IncomeType::class, $income, array(
                    'action' => $this->generateUrl('ajax_update_income'),
                    'attr' => array('class' => 'create_event_form', 'data-id' => $income->getId()),
                    'method' => 'POST',
                ));

                $form->handleRequest($request);

                if($form->isValid()){
                    $raw_data = $form->getData();

                    $income->setDateTime($raw_data->getDateTime());

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($income);
                    $em->flush();

                    $response['success'] = true;
                    return new JsonResponse($response);
                }

                $response['success'] = true;
                $response['form'] = $this->render('BudgetBundle:Default:ajaxIncomeForm.html.twig', [
                    'form' => $form->createView(),
                ])->getContent();

                return new JsonResponse($response);

            }
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }

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
     * @return array - returns array of income and expenses.    ['income' => ['2015-02-02 00:00' => 58.85,],
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
