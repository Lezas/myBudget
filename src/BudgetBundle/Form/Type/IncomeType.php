<?php

namespace BudgetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class IncomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', textType::class, ['label' => 'Income name'])
            ->add('dateTime', datetimetype::class , [
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'format' => 'y-MM-dd HH:mm',
                    'attr' => ['class' => 'date'],
                    'label' => 'Date and time when you spent money',
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