<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EscapeTeamRunType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre projeté',
                'attr' => ['placeholder' => 'Escape par équipes'],
            ])
            ->add('heroImageFile', FileType::class, [
                'label' => 'Image de l\'univers',
                'required' => false,
                'mapped' => false,
                'help' => 'Téléverse un visuel (JPEG ou PNG) qui sera affiché sur la landing et l’attente.',
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('maxTeams', IntegerType::class, [
                'label' => 'Nombre maximum d\'équipes',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('timeLimitMinutes', IntegerType::class, [
                'label' => 'Temps limite (minutes)',
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('step1Solution', TextType::class, [
                'label' => 'Solution étape 1 (mot ou phrase)',
                'attr' => ['placeholder' => 'Mot attendu pour la première épreuve'],
            ])
            ->add('step1Hints', TextareaType::class, [
                'label' => 'Indices étape 1',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Un indice par ligne, affichés aux joueurs sur demande.',
            ])
            ->add('step2Solution', TextType::class, [
                'label' => 'Solution étape 2 (mot ou phrase)',
                'attr' => ['placeholder' => 'Mot attendu pour la seconde épreuve'],
            ])
            ->add('step2Hints', TextareaType::class, [
                'label' => 'Indices étape 2',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Un indice par ligne, affichés aux joueurs sur demande.',
            ])
            ->add('qrSecretWord', TextType::class, [
                'label' => 'Mot secret affiché après scan',
                'required' => true,
                'attr' => ['placeholder' => 'Mot secret révélé par le QR caché'],
                'help' => 'Ce mot sera affiché sur l’appareil qui scanne le QR caché. Les joueurs devront le saisir dans l’étape 4.',
            ])
            ->add('cryptexSolution', TextType::class, [
                'label' => 'Solution finale (cryptex)',
                'attr' => ['placeholder' => 'Mot final pour l’étape cryptex'],
            ])
            ->add('cryptexHints', TextareaType::class, [
                'label' => 'Indices étape 5 (cryptex)',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Un indice par ligne, affichés aux joueurs sur demande.',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'submit_label' => 'Enregistrer',
        ]);

        $resolver->setAllowedTypes('submit_label', 'string');
    }
}
