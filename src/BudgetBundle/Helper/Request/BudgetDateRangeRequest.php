<?php

namespace BudgetBundle\Helper\Request;

use BudgetBundle\Helper\DateTime\DateTimeHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BudgetByDateRangeRequest
 * @package BudgetBundle\Helper\Request
 */
final class BudgetDateRangeRequest
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
        if (null === $this->dateFrom) {
            $dateFrom = $this->getDateRangeVariable('date_from');
            $this->dateFrom = empty($dateFrom) ? $this->dateTimeHelper->getFirstDayOfMonth(new \DateTime('now')) : $dateFrom;
        }

        return $this->dateFrom;
    }


    /**
     * @return \DateTime|null
     */
    public function getDateTo()
    {
        if (null == $this->dateTo) {
            $dateTo = $this->getDateRangeVariable('date_to');
            $this->dateTo = empty($dateTo) ? $this->dateTimeHelper->getLastDayOfMonth(new \DateTime('now')) : $dateTo;
        }

        return $this->dateTo;
    }

    /**
     * @param $variable_name
     * @return \DateTime|string
     */
    protected function getDateRangeVariable($variable_name) {

        $date = '';
        $varValue = $this->getVariable($variable_name);

        if ('lifetime' == mb_strtolower($varValue)) {
            $date = $varValue;
        } elseif (!empty($varValue)) {
            $date = new \DateTime($varValue);
        }

        return $date;
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