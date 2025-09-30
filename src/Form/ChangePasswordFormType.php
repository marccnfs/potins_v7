<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ChangePasswordFormType extends AbstractType
{
    public function __construct(private KernelInterface $kernel) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Contraintes communes (longueur 8 mini)
        $constraints = [
            new NotBlank(['message' => 'Please enter a password']),
            new Length([
                'min' => 8,
                'minMessage' => 'Your password should be at least {{ limit }} characters',
                'max' => 4096,
            ]),
        ];

        // En PROD : on impose la robustesse + le contrôle HIBP
        if ($this->kernel->getEnvironment() === 'prod') {
            $constraints[] = new PasswordStrength(['minScore' => PasswordStrength::STRENGTH_WEAK]); // 1
                // par défaut minScore = MEDIUM (2) ; tu peux expliciter si tu veux
                // 'minScore' => PasswordStrength::STRENGTH_MEDIUM,
            $constraints[] = new NotCompromisedPassword();
        }

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => $constraints,
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'label' => 'confirmez le mot de passe',
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
