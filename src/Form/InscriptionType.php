<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;




class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    $builder

    ->add('groupe', ChoiceType::class, array(
        'choices' => array(
        '1' =>'1',
        '2' =>'2',
        '3' =>'3',
        '4' =>'4'),
        'mapped' =>false
        ));  

    }

     public function getParent(): string
     {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
     }

     public function getBlockPrefix(): string
     {
        return 'app_user_registration';
     }

   /* public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => Appointments::class,
            'data_class' => 'App\Entity\Personnels'
        ));
    }*/
}