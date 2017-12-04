<?php

namespace BudgetBundle\Controller;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReportController
 *
 * @Route("/report", condition="request.isXmlHttpRequest()")
 */
class AjaxReportController extends Controller
{
    /**
     * @Route("/get/income", name="ajax_report_get_income")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxGetIncomeListAction(Request $request)
    {
        $requestData = $this->get('budget.request.daterange');

        $dateFrom = $requestData->getDateFrom();
        $dateTo = $requestData->getDateTo();

        if (mb_strtolower($dateFrom) == "lifetime" && mb_strtolower($dateTo) == "lifetime") {
            return $this->getLifetimeIncomeListAction($request);
        }

        $return = $this->getBudgetList($request, $this->getDoctrine()->getRepository('BudgetBundle:Income'));

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getLifetimeIncomeListAction(Request $request)
    {
        $return = $this->getLifetimeBudget($request, $this->getDoctrine()->getRepository('BudgetBundle:Income'));

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     * @param BudgetRepository $repository
     *
     * @return array
     */
    private function getLifetimeBudget(Request $request, BudgetRepository $repository)
    {
        $user = $this->getUser();
        $Ids = $request->query->get('ids');

        $result = [];
        $sum = 0;
        $IdsArrayCollection = new ArrayCollection();

        if (null != $Ids) {
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
            'dateTo'   => "lifetime",
            'data'     => $result,
            'sum'      => $sum,
        ];

        return $return;
    }

    /**
     * @param ArrayCollection $budgets ArrayCollection of Budget
     *
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
                $budget->getMoney(),
            ];

            $result->add($data);
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param BudgetRepository $repository
     *
     * @return array
     */
    private function getBudgetList(Request $request, BudgetRepository $repository)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $dateRange = $this->get('budget.request.daterange')->getDateRange();
        $Ids = $request->query->get('ids');

        $result = [];
        $sum = 0;
        $IdsArrayCollection = new ArrayCollection();

        if ($Ids != null) {
            $expenses = $repository->getByDateRangeAndCategories($user, $dateRange, $Ids);
            $IdsArrayCollection = new ArrayCollection($Ids);
        } else {
            $expenses = $repository->getByDateRange($user, $dateRange);
        }

        $expenses = new ArrayCollection($expenses);
        $data = $this->createDataFromBudget($expenses);
        $result = array_merge($result, $data->toArray());
        $sum += $this->get('budget.money.counter')->countBudget($expenses);

        if ($IdsArrayCollection->contains("NULL")) {
            $additionalExpenses = $repository->getByDateRangeWithoutCategories($user, $dateRange);
            $expenses = new ArrayCollection($additionalExpenses);

            $data = $this->createDataFromBudget($expenses);
            $result = array_merge($result, $data->toArray());
            $sum += $this->get('budget.money.counter')->countBudget($expenses);
        }

        $return = [
            'dateFrom' => $dateRange->getDateFrom(),
            'dateTo'   => $dateRange->getDateTo(),
            'data'     => $result,
            'sum'      => $sum,
        ];

        return $return;
    }

    /**
     * @Route("/get/expense", name="ajax_report_get_expense")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxGetExpenseListAction(Request $request)
    {
        $requestData = $this->get('budget.request.daterange');

        $dateFrom = $requestData->getDateFrom();
        $dateTo = $requestData->getDateTo();

        if ($dateFrom == "Lifetime" && $dateTo == "Lifetime") {
            return $this->getLifetimeExpenseListAction($request);
        }

        $return = $this->getBudgetList($request, $this->getDoctrine()->getRepository('BudgetBundle:Expenses'));

        return JsonResponse::create($return);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getLifetimeExpenseListAction(Request $request)
    {
        $return = $this->getLifetimeBudget($request, $this->getDoctrine()->getRepository('BudgetBundle:Expenses'));

        return JsonResponse::create($return);
    }
}
