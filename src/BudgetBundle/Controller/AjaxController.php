<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.24
 * Time: 21:41
 */

namespace BudgetBundle\Controller;


use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\DataFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class AjaxController
 * @package BudgetBundle\Controller
 * @Route("/api")
 */
class AjaxController extends Controller
{
    /**
     * @param Request $request
     * @Route("/new-expense", name="ajax_new_expense")
     * @return array|JsonResponse
     */
    public function NewExpenseAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $response['success'] = false;

            if (!$this->isLogged()) {
                $response['cause'] = 'User is not logged in';
                return new JsonResponse($response);
            } else {
                $user = $this->getUser();
                $expense = new Expenses();

                $form = $this->createForm(ExpenseType::class, $expense, array(
                    'action' => $this->generateUrl('ajax_new_expense'),
                    'attr' => array('class' => 'create_budget'),
                    'method' => 'POST',
                    'user' => $user,
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
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }
    }

    /**
     * @param Request $request
     * @Route("/new-income", name="ajax_new_income")
     * @return array|JsonResponse
     */
    public function NewIncomeAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $response['success'] = false;

            if (!$this->isLogged()) {
                $response['cause'] = 'User is not logged in';
                return new JsonResponse($response);
            } else {
                $user = $this->getUser();
                $income = new Income();

                $form = $this->createForm(IncomeType::class, $income, array(
                    'action' => $this->generateUrl('ajax_new_income'),
                    'attr' => array('class' => 'create_budget'),
                    'method' => 'POST',
                    'user' => $user,
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

                    $response['valid'] = true;
                    $response['success'] = true;
                    return new JsonResponse($response);
                }

                $response['valid'] = false;
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
     * @Route("/update-expense/{id}", name="ajax_update_expense")
     * @param null $id
     * @param Request $request
     * @return JsonResponse
     */
    public function UpdateExpense($id = null, Request $request)
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
                    'user' => $user,
                ));

                $form->handleRequest($request);

                if($form->isValid()){
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
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }

    }

    /**
     * @Route("/update-income/{id}", name="ajax_update_income")
     * @param null $id
     * @param Request $request
     * @return JsonResponse
     */
    public function UpdateIncome($id = null, Request $request)
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
                    $response['cause'] = 'Can\'t found income with that id';
                    return new JsonResponse($response);
                }
                if($incomeUser->getId() !== $user->getId()){
                    $response['cause'] = 'Can\'t found income with that id';
                    return new JsonResponse($response);
                }

                $form = $this->createForm(IncomeType::class, $income, array(
                    'action' => $this->generateUrl('ajax_update_income'),
                    'attr' => array('class' => 'create_event_form', 'data-id' => $income->getId()),
                    'method' => 'POST',
                    'user' => $user,
                ));

                $form->handleRequest($request);

                if($form->isValid()){
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
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }

    }

    /**
     * @Route("/delete-income/{id}", name="ajax_delete_income")
     * @param null $id
     * @param Request $request
     * @return JsonResponse
     */
    public function DeleteIncome($id = null, Request $request)
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
                    $response['cause'] = 'Can\'t found income with that id';
                    return new JsonResponse($response);
                }
                if($incomeUser->getId() !== $user->getId()){
                    $response['cause'] = 'Can\'t found income with that id';
                    return new JsonResponse($response);
                }

                $em->remove($income);

                $em->flush();

                $response['success'] = true;

                return new JsonResponse($response);

            }
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }

    }

    /**
     * @Route("/delete-expense/{id}", name="ajax_delete_expense")
     * @param null $id
     * @param Request $request
     * @return JsonResponse
     */
    public function DeleteExpense($id = null, Request $request)
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

                if($expense === null || $expenseUser !== $user){
                    $response['cause'] = 'Can\'t found expense with that id';
                    return new JsonResponse($response);
                }

                $em->remove($expense);

                $em->flush();

                $response['success'] = true;

                return new JsonResponse($response);

            }
        } else {
            throw $this->createNotFoundException('The page doesn\'t exists');
        }

    }

    /**
     * @param Request $request
     * @Route("/expenses-date-range", name="ajax_expense_by_date_range")
     * @return JsonResponse
     */
    public function GetExpenseByDateRangeAction(Request $request)
    {

        if($this->isLogged() && $request->isXmlHttpRequest()) {

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

            $filtered_expense = DataFormatter::groupByDay($expense);

            return JsonResponse::create($filtered_expense);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/income-date-range", name="ajax_income_by_date_range")
     * @return JsonResponse
     */
    public function GetIncomeByDateRangeAction(Request $request)
    {
        if($this->isLogged() && $request->isXmlHttpRequest()) {
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

            $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);

            $filtered_income = DataFormatter::groupByDay($income);

            return JsonResponse::create($filtered_income);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/income-list", name="ajax_income_list_by_date_range")
     * @return JsonResponse
     */
    public function GetIncomeListByDateRangeAction(Request $request)
    {
        if($this->isLogged() && $request->isXmlHttpRequest()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($request->query->get('date_from') != "" || $request->query->get('date_to') != "") {
                $date_from = new \DateTime($request->query->get('date_from'));
                $date_to = new \DateTime($request->query->get('date_to'));
            } else {
                $date = new \DateTime('now');

                //how to get first and last day of the month
                $date_from = new \DateTime(date('Y-m-01', $date->getTimestamp()));
                $date_to = new \DateTime(date('Y-m-t', $date->getTimestamp()));

            }

            $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);
            
            $total = 0;

            foreach($income as $var){
                /** @var Income $var */

                $total += (int)$var->getMoney();
            }

            $response['list'] = $this->render('BudgetBundle:Default:IncomeList.html.twig', [
                'list' => $income,
                'name' => 'income',
            ])->getContent();
            $response['date_from'] = $date_from->format('Y-m-d');
            $response['date_to'] = $date_to->format('Y-m-d');
            $response['total'] = $total;

            return new JsonResponse($response);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/expense-list", name="ajax_expense_list_by_date_range")
     * @return JsonResponse
     */
    public function GetExpenseListByDateRangeAction(Request $request)
    {
        if($this->isLogged() && $request->isXmlHttpRequest()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($request->query->get('date_from') != "" || $request->query->get('date_to') != "") {
                $date_from = new \DateTime($request->query->get('date_from'));
                $date_to = new \DateTime($request->query->get('date_to'));
            } else {
                $date = new \DateTime('now');

                //how to get first and last day of the month
                $date_from = new \DateTime(date('Y-m-01', $date->getTimestamp()));
                $date_to = new \DateTime(date('Y-m-t', $date->getTimestamp()));
            }

            $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

            $total = 0;

            foreach($expense as $var){
                /** @var Expenses $var */
                $total += (int)$var->getMoney();
            }

            $response['list'] = $this->render('BudgetBundle:Default:ExpenseList.html.twig', [
                'list' => $expense,
                'name' => 'expense',
            ])->getContent();
            $response['date_from'] = $date_from->format('Y-m-d');
            $response['date_to'] = $date_to->format('Y-m-d');
            $response['total'] = $total;

            return new JsonResponse($response);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @Route("/expenses", name="ajax_expense")
     */
    public function GetExpenseAction(Request $request)
    {
        if($this->isLogged() && $request->isXmlHttpRequest()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $data = $this->get('budget.repository.budget')->getMonthBudget(null,$user);

            $expense = $data['expenses'];

            $filtered_expense = DataFormatter::groupByDay($expense);
            return JsonResponse::create($filtered_expense);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/budget/chart-data", name="ajax_budget_chart_data")
     * @return JsonResponse
     */
    public function GetBudgetForChart(Request $request){

        if($this->isLogged() && $request->isXmlHttpRequest()) {

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

            $data = DataFormatter::connectData($filteredExpense, $filteredIncome);

            return JsonResponse::create($data);
        }
    }


    /**
     * @return bool - True if user is logged in, false if not.
     */
    private function isLogged()
    {
        $securityContext = $this->get('security.authorization_checker');

        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            return false;
        } else {

            return true;
        }
    }
}