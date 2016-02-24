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

class AjaxController extends Controller
{
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
     * @param Request $request
     * @Route("/api/expenses-date-range", name="ajax_expense_by_date_range")
     * @return JsonResponse
     */
    public function ajaxGetExpenseByDateRangeAction(Request $request)
    {
        if($this->isLogged()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

            $filtered_expense = DataFormatter::groupByDay($expense);

            return JsonResponse::create($filtered_expense);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/api/income-date-range", name="ajax_income_by_date_range")
     * @return JsonResponse
     */
    public function ajaxGetIncomeByDateRangeAction(Request $request)
    {
        if($this->isLogged()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);

            $filtered_income = DataFormatter::groupByDay($income);

            return JsonResponse::create($filtered_income);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/api/income-list", name="ajax_income_list_by_date_range")
     * @return JsonResponse
     */
    public function ajaxGetIncomeListByDateRangeAction(Request $request)
    {
        if($this->isLogged()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $income = $this->getDoctrine()->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);

            $response['list'] = $this->render('BudgetBundle:Default:IncomeList.html.twig', [
                'list' => $income,
                'name' => 'income',
            ])->getContent();

            return new JsonResponse($response);
        }

        throw new NotFoundHttpException("Page not found");
    }

    /**
     * @param Request $request
     * @Route("/api/expense-list", name="ajax_expense_list_by_date_range")
     * @return JsonResponse
     */
    public function ajaxGetExpenseListByDateRangeAction(Request $request)
    {
        if($this->isLogged()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $date_from = new \DateTime($request->query->get('date_from'));
            $date_to = new \DateTime($request->query->get('date_to'));

            $expense = $this->getDoctrine()->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

            $response['list'] = $this->render('BudgetBundle:Default:ExpenseList.html.twig', [
                'list' => $expense,
                'name' => 'expense',
            ])->getContent();

            return new JsonResponse($response);
        }

        throw new NotFoundHttpException("Page not found");
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