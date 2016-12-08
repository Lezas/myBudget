<?php

namespace BudgetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

class dateTimePickerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date_time', datetimetype::class, [
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'y-MM-dd HH:mm',
                'attr' => ['class' => 'date'],
                'label' => false,
            ]
        );

    }

    public function getName()
    {
        return 'dateTimePicker';
    }
}