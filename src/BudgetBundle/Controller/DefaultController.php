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

    /**
     * @Route("/income/new", name="new_income")
     */
    public function newIncomeAction(Request $request)
    {
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

    /**
     * @param Request $request
     * @Route("/expense/new", name="new_expense")
     * @return Response
     */
    public function newExpenseAction(Request $request)
    {
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
