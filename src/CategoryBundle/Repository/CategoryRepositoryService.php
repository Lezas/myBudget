<?php

namespace CategoryBundle\Repository;

use MainBundle\Entity\User;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * CategoryRepository
 */
class CategoryRepositoryService
{
    /** @var ManagerRegistry */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getAllUserIncomeCategories(User $user)
    {
        $income = $this->getAllUserBudgetCategories($user, 'income');

        return $income;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getAllUserExpenseCategories(User $user)
    {
        $expense = $this->getAllUserBudgetCategories($user, 'expense');

        return $expense;
    }

    /**
     * @param User $user
     * @param $BudgetType
     *
     * @return array
     */
    private function getAllUserBudgetCategories(User $user, $BudgetType)
    {
        $em = $this->managerRegistry->getManager();
        $repository = $em->getRepository('CategoryBundle:Category');
        $query = $repository->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->where('c.user = :user')
            ->orWhere('c.user is null')
            ->andWhere('c.type = :type')
            ->orWhere('c.type is null')
            ->andWhere('c.valid = true')
            ->setParameter('user', $user)
            ->setParameter('type', $BudgetType);

        $expense = $query->getQuery()->getResult();

        return $expense;
    }
}
