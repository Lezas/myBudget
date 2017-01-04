<?php

namespace BudgetBundle\Helper\Request;

use BudgetBundle\Helper\DateTime\DateTimeHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BudgetByDateRangeRequest
 * @package BudgetBundle\Helper\Request
 */
final class BudgetByDateRangeRequest
{
    private $request;

    private $dateTimeHelper;

    private $dateTo = null;

    private $dateFrom = null;

    /**
     * BudgetByDateRangeRequest constructor.
     * @param DateTimeHelper $dateTimeHelper
     */
    public function __construct(DateTimeHelper $dateTimeHelper)
    {
        $this->dateTimeHelper = $dateTimeHelper;
    }

    /**
     * @param RequestStack $request_stack
     */
    public function setRequest(RequestStack $request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();

    }

    /**
     * @return \DateTime|null
     */
    public function getDateFrom()
    {
        $dateFrom = $this->dateFrom;
        if ($dateFrom == null) {
            if ($this->getVariable('date_from') != "") {
                $dateFrom = new \DateTime($this->getVariable('date_from'));
            } else {
                $dateFrom = $this->dateTimeHelper->getFirstDayOfMonth(new \DateTime('now'));
            }
        }

        return $dateFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo()
    {
        $dateTo = $this->dateTo;
        if ($dateTo == null) {
            if ($this->getVariable('date_to') != "") {
                $dateTo = new \DateTime($this->getVariable('date_to'));
            } else {
                $dateTo = $this->dateTimeHelper->getLastDayOfMonth(new \DateTime('now'));
            }
        }

        return $dateTo;
    }

    /**
     * @param string $variableName
     * @return string
     */
    protected function getVariable($variableName)
    {
        return $this->request->query->get($variableName);
    }
}