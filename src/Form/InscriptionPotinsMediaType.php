<?php

namespace App\Form;

use App\Entity\Admin\PreOrderResa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class InscriptionPotinsMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('sexe', ChoiceType::class, [
        'choices'=>array(
            'Mme'=>1,
            'Mr'=>2),
        'label' => 'Mme, M...',
        'mapped' => false
    ])
        ->add('name',TextType::class, array(
            'label' => 'Réservé par : (personne référente)'
        ,
            'mapped' => false
        ))
        ->add('email',EmailType::class, array(
            'label' => 'adresse mail',
            'attr' => array(
                'class' => 'validate[required, minSize[3], maxSize[150]] span12'
            ),
            'mapped' => false,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Email(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 150,
                ]),
            ]
        ))
        ->add('telephone',TelType::class, array(
            'label' => 'telephone',
            'attr' => array(
                'class' => 'validate[minSize[10], maxSize[20]] span12'
            ),
            'mapped' => false,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 10,
                    'max' => 20,
                ]),
            ]
        ));

        $builder->add('numberresa', NumberType::class,[
            'label'=>'Nombre de participants',
            'data'=>1,
            'empty_data' => 1,
            'required'=>true,
        ])
        ;

        $builder->add('save', SubmitType::class,[
                'attr'=>['class'=>'btn-send'],
                'label'=>'suivant'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PreOrderResa::class,
        ]);
    }
}
