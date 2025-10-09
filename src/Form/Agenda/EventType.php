<?php

namespace App\Form\Agenda;

use App\Entity\Agenda\Event;
use App\Enum\EventCategory;
use App\Enum\EventVisibility;
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
            ->add('category', F\EnumType::class, [
                'label' => 'Type d’activité',
                'class' => EventCategory::class,
                'choice_label' => fn(EventCategory $c) => $c->label(),
            ])
            ->add('visibility', F\EnumType::class, [
                'label' => 'Visibilité',
                'class' => EventVisibility::class,
                'choice_label' => fn(EventVisibility $v) => match ($v) {
                    EventVisibility::PUBLIC => 'Public',
                    EventVisibility::UNLISTED => 'Non référencé',
                    EventVisibility::PRIVATE => 'Privé',
                },
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
