<?php

namespace BudgetBundle\Helper\Request;

use BudgetBundle\Helper\DateTime\DateTimeHelper;
use BudgetBundle\Model\DateRange;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BudgetByDateRangeRequest
 */
final class BudgetDateRangeRequest
{
    /** @var  */
    private $request;

    /** @var DateTimeHelper */
    private $dateTimeHelper;

    /** @var null */
    private $dateTo = null;

    /** @var null */
    private $dateFrom = null;

    /**
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
     * @return DateRange
     */
    public function getDateRange()
    {
        return new DateRange($this->getDateFrom(), $this->getDateTo());
    }

    /**
     * @param $variable_name
     *
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