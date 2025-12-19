<?php

namespace App\Form;

use App\Entity\Games\EscapeTeamQrPage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EscapeTeamQrPageType extends AbstractType
{
    private const MESSAGES = [
        'Une envie pressante ?',
        'Une petite mousses ?',
        'Dans la boule du sapin',
        'Di hip hop au tango',
        'On peut jouer debout',
        'Elle adoucit les mœurs',
        'Miroir, mon beau miroir...',
        'Entre cour et jardin mon coeur balance',
        'Une petite douche ?',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamName', TextType::class, [
                'label' => "Nom de l'équipe",
                'attr' => ['placeholder' => 'Nom de l’équipe associée'],
            ])
            ->add('identificationCode', TextType::class, [
                'label' => 'Code d’identification (4 chiffres)',
                'attr' => [
                    'maxlength' => 4,
                    'pattern' => '\\d{4}',
                ],
            ])
            ->add('message', ChoiceType::class, [
                'label' => 'Message affiché',
                'choices' => array_combine(self::MESSAGES, self::MESSAGES),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EscapeTeamQrPage::class,
            'submit_label' => 'Enregistrer',
        ]);

        $resolver->setAllowedTypes('submit_label', 'string');
    }
}
