<?php

namespace App\Form;

use App\Entity\UserMap\Taguery;
use App\Entity\Boards\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateAllType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description',TextAreaType::class, array(
                'label' => 'descriptif de votre activité'
            ))
            /*
            ->add('tagueries',CollectionType::class, array(
                'entry_type'=>TagueryType::class,
                'class'=>'App:UserMap\Taguery',
                'allow_add'    => true,
                'allow_delete' => true,
                'required'=>false,
            ))
            */
            ->add('tagueries',TextAreaType::class, array(
                'label' => 'mots clés identifiant votre activité (5 max)',
                'required'=>false,
                'mapped'=>false
            ))

            ->add('baseline',TextType::class, array(
                'label' => 'votre slogan',
                'attr' => array(
                    'class' => 'validate[minSize[3], maxSize[255]] span12'
                ),
                'required'=>false,
            ))
            ->add('activities',TextareaType::class, array(
                'label' => 'parlez de vous...',
                'required'=>false,
            ))
            ->add('emailspaceweb',TextType::class, array(
                'label' => 'Adresse mail (celle qui sera utilisée pour vos relations publiques',
                'attr' => array(
                    'class' => 'validate[ minSize[3], maxSize[255]] span12'
                )
            ))

            ->add('telephonespaceweb',TextType::class, array(
                'label' => 'telephone (fixe)',
                'required'=>false,
            ))
            ->add('telephonemobspaceweb',TextType::class, array(
                'label' => 'telephone (mobile)',
                'required'=>false,
            ))
            ->add('logotemplate',FileType::class, [
                'label'=>"image logo(jpeg)",
                'mapped'=>false,
                'required'=>false,
            ])
            ->add('background',FileType::class, [
                'label'=>"Image de fond(jpeg)",
                'mapped'=>false,
                'required'=>false,
            ])

            //->add('sector', SectorType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Template::class,
        ]);
    }
}
