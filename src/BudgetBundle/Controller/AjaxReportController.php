<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use BudgetBundle\Helper\BudgetMoneyCounter;
use BudgetBundle\Helper\DataFormatter;
use BudgetBundle\Repository\BudgetRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class ReportController
 * @package BudgetBundle\Controller
 * @Route("/report", condition="request.isXmlHttpRequest()")
 */
class AjaxReportController extends Controller
{

    /**
     * @Route("/get/income", name="ajax_report_get_income")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetIncomeListAction(Request $request)
    {
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        if ($dateFrom == "Lifetime" && $dateTo == "Lifetime") {
            return $this->getLifetimeIncomeListAction($request);
        }

        $return = $this->getBudgetList($request, $this->getDoctrine()->getRepository('BudgetBundle:Income'));

        return JsonResponse::create($return);
    }


    /**
     * @Route("/get/expense", name="ajax_report_get_expense")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetExpenseListAction(Request $request)
    {
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        if ($dateFrom == "Lifetime" && $dateTo == "Lifetime") {
            return $this->getLifetimeExpenseListAction($request);
        }

        $return = $this->getBudgetList($request, $this->getDoctrine()->getRepository('BudgetBundle:Expenses'));

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     * @param BudgetRepository $repository
     * @return array
     */
    private function getBudgetList(Request $request, BudgetRepository $repository)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');
        $Ids = $request->query->get('ids');

        $result = [];
        $sum = 0;
        $IdsArrayCollection = new ArrayCollection();

        if ($Ids != null) {
            $expenses = $repository->getByDateRangeAndCategories($user, $dateFrom, $dateTo, $Ids);
            $IdsArrayCollection = new ArrayCollection($Ids);
        } else {
            $expenses = $repository->getByDateRange($user, $dateFrom, $dateTo);
        }

        $expenses = new ArrayCollection($expenses);
        $data = $this->createDataFromBudget($expenses);
        $result = array_merge($result, $data->toArray());
        $sum += $this->get('budget.money.counter')->countBudget($expenses);

        if ($IdsArrayCollection->contains("NULL")) {
            $additionalExpenses = $repository->getByDateRangeWithoutCategories($user, $dateFrom, $dateTo);
            $expenses = new ArrayCollection($additionalExpenses);

            $data = $this->createDataFromBudget($expenses);
            $result = array_merge($result, $data->toArray());
            $sum += $this->get('budget.money.counter')->countBudget($expenses);
        }

        $return = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'data' => $result,
            'sum' => $sum
        ];

        return $return;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLifetimeIncomeListAction(Request $request)
    {
        $return = $this->getLifetimeBudget($request, $this->getDoctrine()->getRepository('BudgetBundle:Income'));

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLifetimeExpenseListAction(Request $request)
    {
        $return = $this->getLifetimeBudget($request, $this->getDoctrine()->getRepository('BudgetBundle:Expenses'));

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     * @param BudgetRepository $repository
     * @return array
     */
    private function getLifetimeBudget(Request $request, BudgetRepository $repository)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $Ids = $request->query->get('ids');

        $result = [];
        $sum = 0;
        $IdsArrayCollection = new ArrayCollection();

        if ($Ids != null) {
            $budget = $repository->getByCategories($user, $Ids);
            $IdsArrayCollection = new ArrayCollection($Ids);
        } else {
            $budget = $repository->getWithCategories($user);
            $budget2 = $repository->getWithoutCategories($user);
            $budget = array_merge($budget, $budget2);
        }

        $budget = new ArrayCollection($budget);

        $data = $this->createDataFromBudget($budget);

        $result = array_merge($result, $data->toArray());
        $sum += $this->get('budget.money.counter')->countBudget($budget);

        if ($IdsArrayCollection->contains("NULL")) {
            $additionalBudget = $repository->getWithoutCategories($user);
            $additionalBudget = new ArrayCollection($additionalBudget);

            $data = $this->createDataFromBudget($additionalBudget);
            $result = array_merge($result, $data->toArray());
            $sum += $this->get('budget.money.counter')->countBudget($additionalBudget);
        }

        $return = [
            'dateFrom' => "lifetime",
            'dateTo' => "lifetime",
            'data' => $result,
            'sum' => $sum
        ];

        return $return;
    }


    /**
     * @param $budgets ArrayCollection of Budget
     * @return ArrayCollection
     */
    private function createDataFromBudget($budgets)
    {
        $result = new ArrayCollection();
        foreach ($budgets as $budget) {
            /** @var Budget $budget */

            $categoryName = "-";
            if ($budget->getCategory() != null) {
                $categoryName = htmlspecialchars($budget->getCategory()->getName());
            }

            $data = [
                $budget->getDateTime()->format('Y-m-d H:i:s'),
                htmlspecialchars($budget->getName()),
                $categoryName,
                $budget->getMoney()
            ];

            $result->add($data);
        }

        return $result;
    }

}
