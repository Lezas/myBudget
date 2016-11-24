<?php

namespace BudgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="income")
 * @ORM\Entity(repositoryClass="BudgetBundle\Repository\IncomeRepository")
 */
class Income extends Budget
{


}
