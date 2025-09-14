<?php

namespace App\Form;

use App\Entity\Module\GpReview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GpReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('titre', TextType::class, array(
                'label' => 'titre',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('subject')

            ->add('save', SubmitType::class,[
                'attr'=>['class'=>'btn-send'],
                'label'=>'enregistrez'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GpReview::class,
        ]);
    }
}
