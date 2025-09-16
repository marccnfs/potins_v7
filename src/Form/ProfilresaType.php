<?php

namespace App\Form;

use App\Entity\Users\ProfilUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilresaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sex', ChoiceType::class, [
                'choices'=>array(
                    'Madame'=>1,
                    'Monsieur'=>2),
                'label' => 'Mme, M...'
            ])
            ->add('firstname', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'prenom',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('emailfirst', EmailType::class, array(
                'label' => 'email',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('telephonemobile',TextType::class, array(
                'label' => 'telephone (mobile)',
                'required'=>false
                ))
            ->add('save',  SubmitType::class,[
                'attr'=>['class'=>'btn-send'],
                'label'=>'enregistrez'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfilUser::class,
        ]);
    }
}
