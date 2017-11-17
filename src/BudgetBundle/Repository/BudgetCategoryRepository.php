<?php

namespace BudgetBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use MainBundle\Entity\User;

/**
 * Class ExpenseCategory
 */
class BudgetCategoryRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var string
     */
    private $budget_type;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param $budget_type
     */
    public function __construct(ManagerRegistry $managerRegistry, $budget_type)
    {
        $this->managerRegistry = $managerRegistry;
        $this->budget_type = $budget_type;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getAllUserBudgetCategories(User $user)
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
            ->setParameter('type', $this->budget_type);

        $expense = $query->getQuery()->getResult();

        return $expense;
    }
}