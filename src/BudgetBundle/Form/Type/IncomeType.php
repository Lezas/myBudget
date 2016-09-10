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

class IncomeType extends AbstractType
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user = null)
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $user = $this->user;
        
        $builder->add('name', textType::class, ['label' => 'Income name'])
            ->add('dateTime', datetimetype::class , [
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'format' => 'y-MM-dd HH:mm',
                    'attr' => ['class' => 'date'],
                    'label' => 'Date and time when you earn money',
                ]
            )
            ->add('money', MoneyType::class)
            ->add('category', EntityType::class, array(
                'label' => 'Category',
                'placeholder' => '-Don\'t have category-',
                'class' => 'CategoryBundle\Entity\Category',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'group_by' => 'parent',
                'query_builder' => function(EntityRepository $er ) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC')
                        ->where('c.user = :user')
                        ->orWhere('c.user is null')
                        ->andWhere('c.type = :income')
                        ->orWhere('c.type is null')
                        ->andWhere('c.valid = true')
                        ->setParameter('user', $user)
                        ->setParameter('income','income');
                },
            ))
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null
        ]);
    }
}