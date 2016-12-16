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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['parents'];
        if ($data == null) {
            $data = [];
        }

        $builder->add('name', TextType::class, ['label' => 'Category name'])
            ->add('valid', CheckboxType::class, [
                    'required' => false,
                    'data' => true,
                ]
            )
            ->add('type', ChoiceType::class, [
                'choices' => ['For expense' => 'expense', 'For Income' => 'income'],
            ])
            ->add('parent', EntityType::class, [
                'label' => 'Parent Category',
                'placeholder' => 'Don\'t have parent category',
                'class' => 'CategoryBundle\Entity\Category',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'group_by' => 'parent',
                'choices' => $data,
            ])
            ->add('submit', SubmitType::class);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'parents' => null,
        ]);
    }
}