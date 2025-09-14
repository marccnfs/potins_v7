<?php

namespace App\Form;

use App\Entity\Member\Boardslist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewMediaBoardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('namewebsite', TextType::class, array(
                'label' => 'Nom de votre website(sociéte, association, marque...',
                'mapped'=>false,
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[100]] span12'
                )
            ))


            ->add('idcity', HiddenType::class,[
                "mapped"=>false
            ])
/*
            ->add('namewebsite', HiddenType::class,[
                "mapped"=>false
            ])
*/
            /*
          ->add('lon', HiddenType::class,[
              "mapped"=>false
          ])
          */
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Boardslist::class,
        ]);
    }
}
