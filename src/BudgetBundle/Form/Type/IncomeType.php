<?php

namespace BudgetBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use MainBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

/**
 * Class IncomeType
 * @package BudgetBundle\Form\Type
 */
class IncomeType extends BudgetType
{

}