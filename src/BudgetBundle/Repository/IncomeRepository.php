<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.24
 * Time: 21:10
 */

namespace BudgetBundle\Repository;


use Doctrine\ORM\EntityRepository;

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
}