<?php
namespace MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;


class ConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('confirm', CheckboxType::class, [
                'label'     => 'Confirm',
                'required'  => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Confirm']);

    }
    public function getName()
    {
        return 'confirmation';
    }
}