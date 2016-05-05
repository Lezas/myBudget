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
            $date1 = new DateTime('2016-01-01');
            $date2 = new DateTime('2016-06-01');
            $budget_array = $repository->getBudgetByDateRange($date1,$date2, $this->getUser());

            $totalIncome = 0;
            $totalExpenses = 0;

            $formater = new DataFormatter();

            dump($budget_array['expenses']);
            dump($formater->groupByDay($budget_array['expenses']));
            //exit;

            foreach($budget_array['income'] as $array){
                $totalIncome += $array->getMoney();
            }

            foreach($budget_array['expenses'] as $array){
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
