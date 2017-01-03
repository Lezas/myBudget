<?php

namespace BudgetBundle\Form\Services;
use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use Symfony\Bridge\Doctrine\ManagerRegistry;


/**
 * Created by PhpStorm.
 * User: Lezas
 * Date: 2017-01-01
 * Time: 21:21
 */
class BudgetFormService
{
    private $managerRegistry;

    /**
     * BudgetRepositoryService constructor.
     * @param ManagerRegistry $managerRegistry
     * @internal param EntityManager $em
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function createBudgetForm(Budget $budget)
    {
        if ($budget instanceof Expenses) {

        }
        if ($budget instanceof Income) {

        }
    }

}