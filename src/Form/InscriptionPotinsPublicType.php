<?php

namespace App\Form;

use App\Entity\Admin\PreOrderResa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class InscriptionPotinsPublicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Mme' => 1,
                    'Mr' => 2,
                ],
                'placeholder' => 'Civilité',
                'label' => 'Civilité',
                'mapped' => false,
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Prénom et nom',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Merci d’indiquer votre nom.']),
                    new Length(['min' => 2, 'max' => 150]),
                ],
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse e-mail',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Merci d’indiquer votre e-mail.']),
                    new Email(['message' => 'Cette adresse e-mail n’est pas valide.']),
                ],
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'tel',
                ],
                'constraints' => [
                    new Length(['min' => 10, 'max' => 20]),
                ],
            ])
            ->add('numberresa', NumberType::class, [
                'label' => 'Nombre de participants',
                'data' => 1,
                'empty_data' => 1,
                'required' => true,
                'html5' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 3,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider mon inscription',
                'attr' => ['class' => 'btn-send'],
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
