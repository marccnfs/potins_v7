<?php

namespace App\Form;

use App\Entity\Customer\Customers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerContactType extends AbstractType
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
            ->add('name',TextType::class, array(
                'label' => 'entrez votre nom d\'utilisateur'
            ))
            ->add('emailcontact',TextType::class, array(
                'label' => 'votre email',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('emailconfirm',TextType::class, array(
                'label' => 'merci de confirmer votre adresse mail',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                ),
                'mapped' => false
            ))
            ->add('telephonecustomer',TextType::class, array(
                'label' => 'telephone',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[10]] span12'
                )
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
