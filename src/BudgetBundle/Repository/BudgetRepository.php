<?php

namespace BudgetBundle\Repository;

use MainBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * Class BudgetRepository
 */
class BudgetRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param $date_from
     * @param $date_to
     *
     * @return array
     */
    public function getByDateRange(User $user, $date_from, $date_to)
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
            ->setParameter('date_from', $date_from)
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
     *
     * @return mixed ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeAndCategories($user, $date_from, $date_to, array $categoryIds)
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
            ->setParameter('date_from', $date_from)
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
     *
     * @return mixed  ['2015-02-02 00:00' => 58.85,]
     */
    public function getByDateRangeWithoutCategories($user, $date_from, $date_to)
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
            ->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
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