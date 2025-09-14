<?php

namespace App\Form;

use App\Entity\Module\PostEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, array(
                'label' => 'Nom du marché',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('adress', null, array(
                'label' => 'Lieu',
                'required' => true,
                'attr' => array(
                    'class' => 'validate[required] span12',
                    'data-google' => 'text',
                    'infos' => "localisation du marché."
                ),
                'mapped'=>false,
            ))
            ->add('soustitre')
            ->add('description')
            ->add('contenuone')

            ->add('street_number', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('route', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('country', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('latitude', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('longitude', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('locality', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('postal_code', HiddenType::class, array(
                'mapped'=> false
            ))

            ->add('save', SubmitType::class);

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostEvent::class,
        ]);
    }
}
