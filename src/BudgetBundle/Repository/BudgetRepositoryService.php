<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.25
 * Time: 11:39
 */

namespace BudgetBundle\Repository;


use Doctrine\ORM\EntityManager;
use MainBundle\Entity\User;
use Symfony\Component\Validator\Constraints\Date;

class BudgetRepositoryService
{
    private $_em;

    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }

    /**
     *
     * @param Date $date - date object MUST be valid datetime object or string of format YYYY-MM-DD
     * @return array
     */
    public function getMonthBudget(Date $date = null, User $user)
    {
        //if $dat is null, return this month budget
        if($date === null){
            $date = new \DateTime('now');
        }

        //how to get first and last day of the month
        $month_first_day = date('Y-m-01', $date->getTimestamp());
        $month_last_day = date('Y-m-t', $date->getTimestamp());

        $budget_array = $this->getBudgetByDateRange($month_first_day, $month_last_day, $user);

        return $budget_array;
    }

    /**
     * @param $date_from
     * @param $date_to
     * @param $user
     * @return array
     */
    public function getBudgetByDateRange($date_from, $date_to, $user)
    {
        $income = $this->_em->getRepository('BudgetBundle:Income')->getByDateRange($user, $date_from, $date_to);
        $expense = $this->_em->getRepository('BudgetBundle:Expenses')->getByDateRange($user, $date_from, $date_to);

        $budget = array();

        $budget['income'] = $income;
        $budget['expenses'] = $expense;

        return $budget;
    }
}