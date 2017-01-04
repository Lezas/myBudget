<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.25
 * Time: 11:39
 */

namespace BudgetBundle\Repository;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Helper\DateTime\DateTimeHelper;
use Doctrine\ORM\EntityManager;
use MainBundle\Entity\User;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class BudgetRepositoryService
 * @package BudgetBundle\Repository
 */
class BudgetRepositoryService
{
    private $managerRegistry;

    private $dateTimeHelper;

    /**
     * BudgetRepositoryService constructor.
     * @param ManagerRegistry $managerRegistry
     * @param DateTimeHelper $dateTimeHelper
     * @internal param EntityManager $em
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

        $budget_array = $this->getBudgetByDateRange($month_first_day, $month_last_day, $user);

        return $budget_array;
    }

    /**
     * @param string $date_from
     * @param string $date_to
     * @param User $user
     * @return array
     */
    public function getBudgetByDateRange($date_from, $date_to, $user)
    {
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