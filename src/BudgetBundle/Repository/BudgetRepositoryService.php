<?php

namespace BudgetBundle\Repository;

use BudgetBundle\Helper\DateTime\DateTimeHelper;
use BudgetBundle\Model\DateRange;
use Doctrine\ORM\EntityManager;
use MainBundle\Entity\User;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class BudgetRepositoryService
 */
class BudgetRepositoryService
{
    /** @var ManagerRegistry */
    private $managerRegistry;

    /** @var DateTimeHelper */
    private $dateTimeHelper;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param DateTimeHelper $dateTimeHelper
     */
    public function __construct(ManagerRegistry $managerRegistry, DateTimeHelper $dateTimeHelper)
    {
        $this->managerRegistry = $managerRegistry;
        $this->dateTimeHelper = $dateTimeHelper;
    }

    /**
     *
     * @param \DateTime|Date $date - date object MUST be valid datetime object or string of format YYYY-MM-DD
     * @param User $user
     *
     * @return array
     */
    public function getMonthBudget(\DateTime $date = null, User $user)
    {
        //if $dat is null, return this month budget
        if ($date === null) {
            $date = new \DateTime('now');
        }

        //how to get first and last day of the month
        $month_first_day = $this->dateTimeHelper->getFirstDayOfMonth($date);
        $month_last_day = $this->dateTimeHelper->getLastDayOfMonth($date);
        $dateRange = new DateRange($month_first_day, $month_last_day);

        $budget_array = $this->getBudgetByDateRange($dateRange, $user);

        return $budget_array;
    }

    /**
     * @param DateRange $dateRange
     * @param User $user
     *
     * @return array
     */
    public function getMonthBudgetByDateRange(DateRange $dateRange, User $user)
    {
        $month_first_day = $this->dateTimeHelper->getFirstDayOfMonth($dateRange->getDateFrom());
        $month_last_day = $this->dateTimeHelper->getLastDayOfMonth($dateRange->getDateTo());

        $dateRange = new DateRange($month_first_day, $month_last_day);

        $budget_array = $this->getBudgetByDateRange($dateRange, $user);

        return $budget_array;
    }

    /**
     * @param DateRange $dateRange
     * @param $user
     *
     * @return array
     */
    public function getBudgetByDateRange(DateRange $dateRange, $user)
    {
        $date_from = $dateRange->getDateFrom();
        $date_to = $dateRange->getDateTo();

        /** @var EntityManager $em */
        $em = $this->managerRegistry->getManager();
        $income = $em->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);
        $expense = $em->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

        $budget = [];

        $budget['income'] = $income;
        $budget['expenses'] = $expense;

        return $budget;
    }
}