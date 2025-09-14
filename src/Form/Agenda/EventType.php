<?php

namespace App\Form\Agenda;

use App\Entity\Agenda\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as F;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b
            ->add('title', F\TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', F\TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            // champs locaux (affichés au user) — tu convertiras en UTC dans le contrôleur
            ->add('startsAtLocal', F\DateTimeType::class, [
                'label' => 'Début (heure locale)',
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('endsAtLocal', F\DateTimeType::class, [
                'label' => 'Fin (heure locale)',
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('timezone', F\TextType::class, [
                'label' => 'Fuseau',
                'empty_data' => 'Europe/Paris',
            ])
            ->add('isAllDay', F\CheckboxType::class, [
                'label' => 'Toute la journée',
                'required' => false,
            ])
            ->add('locationName', F\TextType::class, [
                'label' => 'Lieu',
                'required' => false,
            ])
            ->add('locationAddress', F\TextType::class, [
                'label' => 'Adresse',
                'required' => false,
            ])
            ->add('communeCode', F\ChoiceType::class, [
                'label' => 'Commune',
                'choices' => [
                    'Le Pellerin' => 'pellerin',
                    'La Montagne' => 'montagne',
                    'Saint-Jean-de-Boiseau' => 'sjb',
                    'Autre / Hors zone' => 'autre',
                ],
            ])
            ->add('capacity', F\IntegerType::class, [
                'label' => 'Capacité (laisser vide = illimité)',
                'required' => false,
            ])
            ->add('category', F\ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Atelier' => 'atelier',
                    'RDV' => 'rdv',
                    'Externe' => 'externe',
                    'Autre' => 'autre',
                ],
            ])
            ->add('visibility', F\ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Public' => 'public',
                    'Non référencé' => 'unlisted',
                    'Privé' => 'private',
                ],
            ])
            ->add('published', F\CheckboxType::class, [
                'label' => 'Publié',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Event::class]);
    }
}
