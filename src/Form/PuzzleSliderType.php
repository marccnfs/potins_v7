<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PuzzleSliderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cfg = \is_array($options['config']) ? $options['config'] : [];
        $hintsData = $cfg['hints'] ?? ['']; // prérempli 1 ligne vide si rien

        $builder
            // Métadonnées (facultatives)
            ->add('title', TextType::class, [
                'label'   => 'Titre de l’épreuve',
                'mapped'  => false,
                'required'=> false,
                'data'    => $cfg['title'] ?? null,
            ])
            ->add('prompt', TextType::class, [
                'label'   => 'Consigne',
                'mapped'  => false,
                'required'=> false,
                'data'    => $cfg['prompt'] ?? null,
            ])

            // Image (optionnelle en édition)
            ->add('imageFile', FileType::class, [
                'label'    => 'Image (jpg/png/webp)',
                'mapped'   => false,
                'required' => false, // IMPORTANT : pas obligatoire si déjà en base
                'constraints' => [
                    new FileConstraint([
                        'maxSize'           => '16M',
                        'mimeTypes'         => ['image/jpeg','image/png','image/webp'],
                        'mimeTypesMessage'  => 'Formats acceptés : JPG/PNG/WebP.',
                    ]),
                ],
                'help' => '1600 px de large conseillé pour un bon rendu.',
            ])

            // Paramètres de grille
            ->add('rows', IntegerType::class, [
                'label'    => 'Lignes',
                'mapped'   => false,
                'required' => false,
                'data'     => $cfg['rows'] ?? 3,
                'constraints' => [ new Range(['min'=>2, 'max'=>10, 'notInRangeMessage'=>'Choisir entre 2 et 10.']) ],
                'attr' => ['min'=>2, 'max'=>10],
            ])
            ->add('cols', IntegerType::class, [
                'label'    => 'Colonnes',
                'mapped'   => false,
                'required' => false,
                'data'     => $cfg['cols'] ?? 3,
                'constraints' => [ new Range(['min'=>2, 'max'=>10, 'notInRangeMessage'=>'Choisir entre 2 et 10.']) ],
                'attr' => ['min'=>2, 'max'=>10],
            ])

            ->add('okMessage', TextType::class, [
                'label'    => 'Message de réussite',
                'mapped'   => false,
                'required' => false,
                'data'     => $cfg['okMessage'] ?? 'Bravo !',
            ])
            ->add('finalClue', TextareaType::class, [
                'label'    => 'Indice final',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['rows' => 2],
                'data'     => $cfg['finalClue'] ?? '',
                'help'     => 'Fragment délivré une fois le puzzle reconstitué.',
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
