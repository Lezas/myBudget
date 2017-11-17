<?php

namespace BudgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="expense")
 * @ORM\Entity(repositoryClass="BudgetBundle\Repository\ExpensesRepository")
 */
class Expenses extends Budget
{
}
