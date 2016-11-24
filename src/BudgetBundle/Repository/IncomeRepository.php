<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.24
 * Time: 21:10
 */

namespace BudgetBundle\Repository;


use Doctrine\ORM\EntityRepository;
use MainBundle\Entity\User;

class IncomeRepository extends EntityRepository
{
    /**
     * @param $date_from
     * @param $date_to
     * @return mixed  ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRange($user, $date_from, $date_to)
    {

        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            ORDER BY p.dateTime'
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $income = $query->getResult();

        return $income;
    }

    /**
     * @param $date_from
     * @param $date_to
     * @return mixed  ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeAndCategories($user, $date_from, $date_to, array $Categoryids)
    {

        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            AND p.category IN (:ids)
            ORDER BY p.dateTime'
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user)
            ->setParameter('ids', $Categoryids);

        $income = $query->getResult();

        return $income;
    }

    /**
     * @param $date_from
     * @param $date_to
     * @return mixed  ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeWithoutCategories($user, $date_from, $date_to)
    {

        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            AND p.category is null
            ORDER BY p.dateTime'
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $income = $query->getResult();

        return $income;
    }

    public function getFirstIncome(User $user)
    {

        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE p.user = :id
            ORDER BY p.dateTime ASC'
        )->setParameter('id', $user)
            ->setMaxResults(1);

        $income = $query->getResult();

        return $income;
    }


    /**
     * @param User $user
     * @param array $CategoryIds
     * @return array
     */
    public function getByCategories(User $user, array $CategoryIds)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE p.user = :id
            AND p.category IN (:ids)
            ORDER BY p.dateTime'
        )->setParameter('id', $user)
            ->setParameter('ids', $CategoryIds);

        $expense = $query->getResult();

        return $expense;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getWithoutCategories(User $user)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE p.user = :id
            AND p.category is null
            ORDER BY p.dateTime'
        )->setParameter('id', $user);

        $expense = $query->getResult();

        return $expense;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getIncomeWithCategories(User $user)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Income p
            WHERE p.user = :id
            AND p.category is not null
            ORDER BY p.dateTime'
        )->setParameter('id', $user);

        $income = $query->getResult();

        return $income;
    }
}