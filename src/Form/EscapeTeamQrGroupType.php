<?php

namespace App\Form;

use App\Entity\Games\EscapeTeamQrGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EscapeTeamQrGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du groupe QR',
                'attr' => [
                    'placeholder' => 'Groupe de QR équipes',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EscapeTeamQrGroup::class,
            'submit_label' => 'Enregistrer',
        ]);

        $resolver->setAllowedTypes('submit_label', 'string');
    }
}
