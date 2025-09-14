<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PuzzleCryptexType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cfg = \is_array($options['config']) ? $options['config'] : [];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l’énigme',
                'mapped' => false,
                'data'    => $cfg['title'] ?? null,
            ])

            ->add('prompt', TextareaType::class, [
                'label' => 'Consigne',
                'required' => false,
                'mapped' => false,
                'data'    => $cfg['prompt'] ?? null,
                ])

            ->add('solution', TextType::class, [
                'label' => 'Solution (ex: LIVRE)',
                'mapped' => false,
                'data'    => $cfg['solution'] ?? null,
            ])
            ->add('hashMode', CheckboxType::class, [
                'label' => 'Masquer la solution (hash SHA-256 côté client)',
                'required' => false,
                'mapped' => false,
                'data'    => $cfg['hashMode'] ?? false,
            ])
            ->add('alphabet', TextType::class, [
                'label' => 'Alphabet',
                'empty_data' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'mapped' => false,
                'data'    => $cfg['alphabet'] ?? null,
            ])
            ->add('scramble', CheckboxType::class, [
                'label' => 'Mélanger au départ', 'required' => false,
                'mapped' => false,
                'data'    => $cfg['scramble'] ?? true,
            ])
            ->add('autocheck', CheckboxType::class, [
                'label' => 'Validation automatique', 'required' => false,
                'mapped' => false,
                'data'    => $cfg['autocheck'] ?? true,
            ])
            ->add('successMessage', TextareaType::class, [
                'label' => 'Message de réussite (indice suivant)',
                'required' => false,
                'mapped' => false,
                'data'     => $cfg['successMessage'] ?? 'Bravo !',
            ])
            ->add('hintsJson', TextareaType::class, [
                'label'    => 'Indices (JSON)',
                'mapped'   => false,
                'required' => true, // on force à fournir quelque chose
                'attr'     => [
                    'rows' => 3,
                    'placeholder' => '["Commence par les bords","Repère les couleurs"]',
                    'spellcheck' => 'false'
                ],
                'help'     => 'Mettre un tableau JSON de chaînes : ["Indice 1","Indice 2"]. Au moins 1 indice requis (impacte le score).',
                'data'     => isset($cfg['hints'])
                    ? json_encode($cfg['hints'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
                    : "",
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Ajoute au moins un indice.']),
                    // Validation JSON + ≥1 item
                    new Assert\Callback(function($value, ExecutionContextInterface $ctx) {
                        if ($value === null) return;
                        $value = trim((string)$value);
                        // tolère un JSON vide "[]", mais on exigera ≥1 item
                        $data = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $ctx->buildViolation('Le champ doit contenir un JSON valide (ex: ["Indice 1","Indice 2"]).')
                                ->addViolation();
                            return;
                        }
                        if (!is_array($data)) {
                            $ctx->buildViolation('Le JSON doit être un tableau de chaînes.')
                                ->addViolation();
                            return;
                        }
                        // filtre les chaînes vides
                        $clean = array_values(array_filter(array_map(static fn($s)=>trim((string)$s), $data), static fn($s)=>$s!==''));
                        if (count($clean) < 1) {
                            $ctx->buildViolation('Ajoute au moins un indice non vide.')
                                ->addViolation();
                        }
                    }),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // on construit config nous-mêmes
            'config'     => [],   // pré-remplissage
        ]);
    }
}
