<?php

namespace BudgetBundle\Model;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class DateRange
 */
class DateRange
{
    /** @var \DateTime */
    private $dateFrom;

    /** @var \DateTime */
    private $dateTo;

    /**
     * DateRange constructor.
     *
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     */
    public function __construct(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

        $this->assertDateRangeIsValid();
    }

    private function assertDateRangeIsValid()
    {
        $dateFromTimestamp = $this->dateFrom->getTimestamp();
        $dateToTimestamp = $this->dateTo->getTimestamp();

        if ($dateFromTimestamp > $dateToTimestamp) {
            throw new Exception('dateFrom cannot be higher than dateTo.');
        }
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }
}