<?php
namespace CategoryBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use MainBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CategoryType
 * @package CategoryBundle\Form\Type
 */
class CategoryType extends AbstractType
{
    /**
     * @var User
     */
    private $user;

    /**
     * CategoryType constructor.
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
        $this->user = $options['user'];

        $user = $this->user;

        $builder->add('name', TextType::class, ['label' => 'Category name'])
            ->add('valid', CheckboxType::class, array(
                    'required' => false
                )
            )
            ->add('type', ChoiceType::class, array(
                'choices' => array('For expense' => 'expense', 'For Income' => 'income'),
                'choices_as_values' => true,
            ))
            ->add('parent', EntityType::class, array(
                'label' => 'Parent Category',
                'placeholder' => 'Don\'t have parent category',
                'class' => 'CategoryBundle\Entity\Category',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'group_by' => 'parent',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC')
                        ->where('c.user = :user')
                        ->orWhere('c.user is null')
                        ->andWhere('c.parent is null')
                        ->setParameter('user', $user);
                },
            ))
            ->add('submit', SubmitType::class);

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CategoryBundle/Entity/Category',
            'user' => null
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