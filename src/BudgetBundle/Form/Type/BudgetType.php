<?php

namespace BudgetBundle\Form\Type;

use MainBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class BudgetType
 */
class BudgetType extends AbstractType
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User|null $user
     */
    public function __construct(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['categories'];
        if ($data == null) {
            $data = [];
        }

        $builder->add('name', TextType::class, [
            'label' => 'Name',
            'required' => true,
        ])
            ->add('dateTime', DateTimeType::class, [
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'format' => 'y-MM-dd HH:mm',
                    'attr' => ['class' => 'date'],
                    'label' => 'Date and time',
                    'required' => true,

                ]
            )
            ->add('money', MoneyType::class, [
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'label' => 'Category',
                'placeholder' => '-Don\'t have category-',
                'class' => 'CategoryBundle\Entity\Category',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'group_by' => 'parent',
                'choices' => $data,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save']);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'categories' => null,
        ]);
    }
}