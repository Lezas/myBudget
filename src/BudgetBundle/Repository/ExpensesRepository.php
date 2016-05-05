<?php
/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.24
 * Time: 20:47
 */

namespace BudgetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MainBundle\Entity\User;

class ExpensesRepository extends EntityRepository
{

    public function getByDateRange(User $user, $date_from, $date_to)
    {
        $query = $this->_em->createQuery(
            'SELECT p
            FROM BudgetBundle:Expenses p
            WHERE (p.dateTime BETWEEN :date_from AND :date_to)
            AND p.user = :id
            ORDER BY p.dateTime
            '
        )->setParameter('date_from', $date_from)
            ->setParameter('date_to', $date_to)
            ->setParameter('id', $user);

        $expenses = $query->getResult();

        return $expenses;
    }
}