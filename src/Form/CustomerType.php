<?php

namespace App\Form;

use App\Entity\Customer\Customers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('sexe', ChoiceType::class, [
                'choices'=>array(
                    'Madame'=>1,
                    'Monsieur'=>2),
                'label' => 'Mme, M...',
                'mapped' => false
            ])
            ->add('firstname', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                ),
                'mapped' => false
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'prenom',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                ),
                'mapped' => false
            ))

            ->add('telephone',TextType::class, array(
                'label' => 'telephone',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[10]] span12'
                ),
                'mapped' => false
            ))

            ->add('email',TextType::class, array(
                'label' => 'adresse mail',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                ),
                'mapped' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customers::class,
        ]);
    }
}
