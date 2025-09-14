<?php

namespace App\Form;

use App\Entity\Customer\Customers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*
            ->add('namespaceweb', TextType::class, array(
                'label' => 'Nom de votre Pass sur AffiChange',
                'mapped'=>false,
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[70]] span12'
                )
            ))
            */
                /*
            ->add('charte', CheckboxType::class,[
                'label'=>false,
                'required'=>false,
                'mapped'=>false
            ])
                */
            ->add('codep', HiddenType::class, array(
                'mapped' => false
            ))
            ->add('city', HiddenType::class, array(
                'mapped' => false
            ))
            ->add('typespaceweb', HiddenType::class,[
                "mapped"=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customers::class,
        ]);
    }
}
