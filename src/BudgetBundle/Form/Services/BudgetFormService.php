<?php

namespace BudgetBundle\Form\Services;

use BudgetBundle\Entity\Budget;
use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Form\Type\ExpenseType;
use BudgetBundle\Form\Type\IncomeType;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;

/**
 * Class BudgetFormService
 * @package BudgetBundle\Form\Services
 */
class BudgetFormService
{

    private $container;

    /**
     * BudgetRepositoryService constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {

        $this->container = $container;
    }

    /**
     * @param Budget $budget
     * @param $action
     * @param $user
     * @return Form|null
     */
    public function createBudgetForm(Budget $budget, $action, $user)
    {
        $formFactory = $this->container->get('form.factory');
        $form = null;

        if ($budget instanceof Expenses) {

            $categories = $this->container->get('category.repository.service')->getAllUserExpenseCategories($user);

            $form = $formFactory->create(ExpenseType::class, $budget, [
                'action' => $action,
                'attr' => ['class' => 'create_budget'],
                'method' => 'POST',
                'categories' => $categories,
            ]);
        }

        if ($budget instanceof Income) {
            $categories = $this->container->get('category.repository.service')->getAllUserIncomeCategories($user);

            $form = $formFactory->create(IncomeType::class, $budget, [
                'action' => $action,
                'attr' => ['class' => 'create_budget'],
                'method' => 'POST',
                'categories' => $categories,
            ]);
        }

        return $form;
    }


}