<?php

namespace BudgetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BudgetBundle\Form\Type\dateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class IncomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', textType::class, ['label' => 'Income name'])
            ->add('date_time', dateTimePickerType::class , [
                    'label' => 'Date ant time when earned money',
                ]
            )
            ->add('money', MoneyType::class)
            ->add('submit', submitType::class, ['label' => 'Save']);

    }
    public function getName()
    {
        return 'income';
    }
    public function setDefaultOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'BudgetBundle/Entity/Income'
        ]);
    }
}