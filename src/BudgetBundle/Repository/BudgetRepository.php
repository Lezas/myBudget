<?php

namespace BudgetBundle\Repository;

use BudgetBundle\Model\DateRange;
use MainBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * Class BudgetRepository
 */
class BudgetRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param DateRange $dateRange
     *
     * @return array
     */
    public function getByDateRange(User $user, DateRange $dateRange)
    {
        $entityName = $this->getEntityName();

        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE (p.dateTime BETWEEN :date_from AND :date_to)
AND p.user = :id
ORDER BY p.dateTime
SQL;

        $query = $this->_em->createQuery($sql)
            ->setParameter('date_from', $dateRange->getDateFrom())
            ->setParameter('date_to', $dateRange->getDateTo())
            ->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     * @param DateRange $dateRange
     * @param array $categoryIds
     *
     * @return mixed ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeAndCategories($user, DateRange $dateRange, array $categoryIds)
    {
        $entityName = $this->getEntityName();
        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE (p.dateTime BETWEEN :date_from AND :date_to)
AND p.user = :id
AND p.category IN (:ids)
ORDER BY p.dateTime
SQL;

        $query = $this->_em->createQuery($sql)
            ->setParameter('date_from', $dateRange->getDateFrom())
            ->setParameter('date_to', $dateRange->getDateTo())
            ->setParameter('id', $user)
            ->setParameter('ids', $categoryIds);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param $user User
     * @param DateRange $dateRange
     *
     * @return mixed ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeWithoutCategories($user, DateRange $dateRange)
    {
        $entityName = $this->getEntityName();

        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE (p.dateTime BETWEEN :date_from AND :date_to)
AND p.user = :id
AND p.category is null
ORDER BY p.dateTime
SQL;

        $query = $this->_em->createQuery($sql)
            ->setParameter('date_from', $dateRange->getDateFrom())
            ->setParameter('date_to', $dateRange->getDateTo())
            ->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getFirst(User $user)
    {
        $entityName = $this->getEntityName();

        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE p.user = :id
ORDER BY p.dateTime ASC
SQL;
        $query = $this->_em->createQuery($sql)
            ->setParameter('id', $user)
            ->setMaxResults(1);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     * @param array $CategoryIds
     *
     * @return array
     */
    public function getByCategories(User $user, array $CategoryIds)
    {
        $entityName = $this->getEntityName();

        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE p.user = :id
AND p.category IN (:ids)
ORDER BY p.dateTime
SQL;
        $query = $this->_em->createQuery($sql)
            ->setParameter('id', $user)
            ->setParameter('ids', $CategoryIds);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getWithoutCategories(User $user)
    {
        $entityName = $this->getEntityName();

        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE p.user = :id
AND p.category is null
ORDER BY p.dateTime
SQL;
        $query = $this->_em->createQuery($sql)
            ->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getWithCategories(User $user)
    {
        $entityName = $this->getEntityName();

        $sql = <<<SQL
SELECT p
FROM $entityName p
WHERE p.user = :id
AND p.category is not null
ORDER BY p.dateTime
SQL;

        $query = $this->_em->createQuery($sql)
            ->setParameter('id', $user);

        $budget = $query->getResult();

        return $budget;
    }
}