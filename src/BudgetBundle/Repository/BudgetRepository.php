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
use Doctrine\ORM\EntityRepository;

/**
 * Class BudgetRepository
 * @package BudgetBundle\Repository
 */
class BudgetRepository extends EntityRepository
{

    /**
     * @param User $user
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public function getByDateRange(User $user, $date_from, $date_to)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM '. $this->getEntityName() .' p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            ORDER BY p.dateTime
            '
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     * @param string $date_from
     * @param string $date_to
     * @param array $categoryIds
     * @return mixed ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeAndCategories($user, $date_from, $date_to, array $categoryIds)
    {

        $query = $this->_em->createQuery(
            'SELECT p
            FROM '. $this->getEntityName() .' p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            AND p.category IN (:ids)
            ORDER BY p.dateTime'
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user)
            ->setParameter('ids', $categoryIds);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param $date_from \DateTime
     * @param $user User
     * @param $date_to \DateTime
     * @return mixed  ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeWithoutCategories($user, $date_from, $date_to)
    {

        $query = $this->_em->createQuery(
            'SELECT p
            FROM '. $this->getEntityName() .' p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            AND p.category is null
            ORDER BY p.dateTime'
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getFirst(User $user)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM '. $this->getEntityName() .' p
            WHERE p.user = :id
            ORDER BY p.dateTime ASC'
        )->setParameter('id', $user)
            ->setMaxResults(1);

        $budget = $query->getResult();

        return $budget;
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
            FROM '. $this->getEntityName() .' p
            WHERE p.user = :id
            AND p.category IN (:ids)
            ORDER BY p.dateTime'
        )->setParameter('id', $user)
            ->setParameter('ids', $CategoryIds);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getWithoutCategories(User $user)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM '. $this->getEntityName() .' p
            WHERE p.user = :id
            AND p.category is null
            ORDER BY p.dateTime'
        )->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getWithCategories(User $user)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM '. $this->getEntityName() .' p
            WHERE p.user = :id
            AND p.category is not null
            ORDER BY p.dateTime'
        )->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }
}