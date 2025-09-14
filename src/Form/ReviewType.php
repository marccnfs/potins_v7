<?php

namespace App\Form;

use App\Entity\Ressources\Ressources;
use App\Entity\Ressources\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre',TextType::class, array(
                'label' => "Titre de l`\article",
                'attr' => array(
                    'class' => 'affi12'
                ),
                'constraints' => [new Length(['min' => 3, 'max'=>250])],
                'required'=>true,
            ))
            /*
            ->add('prix',MoneyType::class, [
                'label'    => 'Prix',
                'html5' => false,
                'required' => false,
                'attr'  =>  array('maxlength' => 10, 'placeholder' => '0.00',  'data-thousands'=>'€')
            ])
            */
           ->add('categorie', EntityType::class, [
                'class'=>Categories::class,
                'choice_label' => function ($categorie) {
                    return $categorie->getName();},
                'multiple'=>false,
                'expanded'=>false
            ])
            ->add('composition',TextareaType::class, array(
                'label' => 'Titre :',
                'attr' => array(
                    'class' => 'affi12'
                ),
                'constraints' => [new Length(['min' => 3, 'max'=>250])],
                'required'=>false,
            ))
            ->add('descriptif',TextareaType::class, array(
                'label' => 'Description :',
                'attr' => array(
                    'class' => 'affi12'
                ),
                'required'=>false,
            ))

            ->add('pict',PictType::class, [
                'label' => 'image',
                'required'=>false,
                'mapped'=>false])

            ->add('base64',HiddenType::class, [
                'label' => 'image 64',
                'required'=>false,
                'mapped'=>false])

            ->add('infos',TextareaType::class, array(
                'label' => 'liens :',
                'attr' => array(
                    'class' => 'affi12'
                ),
                'constraints' => [new Length(['min' => 3, 'max'=>250])],
                'required'=>false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ressources::class,
        ]);
    }
}
