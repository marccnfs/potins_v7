<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Games\EscapeWorkshopSession;
use App\Entity\Module\PostEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EscapeWorkshopSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de la session',
                'attr' => ['placeholder' => 'Ex. Groupe du matin'],
            ])
            ->add('event', EntityType::class, [
                'label' => 'Atelier associé',
                'class' => PostEvent::class,
                'choices' => $options['escape_events'],
                'choice_label' => static function (?PostEvent $event): string {
                    if (!$event instanceof PostEvent) {
                        return '';
                    }

                    $title = $event->getTitre() ?? 'Atelier sans titre';
                    $start = $event->getAppointment()?->getStarttime();

                    return $start ? sprintf('%s — %s', $title, $start->format('d/m/Y H:i')) : $title;
                },
                'placeholder' => 'Sélectionnez un atelier',
                'required' => false,
                'choice_value' => 'id',
            ])
            ->add('isMaster', CheckboxType::class, [
                'label' => 'Code maître (permet des créations hors atelier)',
                'required' => false,
            ])
            ->add('customCode', TextType::class, [
                'label' => 'Code personnalisé',
                'required' => false,
                'mapped' => false,
                'attr' => ['maxlength' => 16, 'placeholder' => 'Optionnel'],
                'help' => 'Laissez vide pour générer automatiquement un code à 4 chiffres.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EscapeWorkshopSession::class,
            'escape_events' => [],
        ]);
        $resolver->setAllowedTypes('escape_events', ['array']);
    }
}
