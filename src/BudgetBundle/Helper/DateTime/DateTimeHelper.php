<?php

namespace BudgetBundle\Helper\DateTime;

use DateTime;

/**
 * Class DateTimeHelper
 */
class DateTimeHelper
{
    /**
     * @param DateTime $dateTime
     *
     * @return DateTime
     */
    public function getFirstDayOfMonth(\DateTime $dateTime)
    {
        return  new \DateTime(date('Y-m-01 00:00', $dateTime->getTimestamp()));
    }

    /**
     * @param DateTime $dateTime
     *
     * @return DateTime
     */
    public function getLastDayOfMonth(\DateTime $dateTime)
    {
        return  new \DateTime(date('Y-m-t 23:59', $dateTime->getTimestamp()));
    }
}