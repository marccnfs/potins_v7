<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PuzzleVideoQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cfg = \is_array($options['config']) ? $options['config'] : [];

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

            // Fichier vidéo (optionnel en édition)
            ->add('videoFile', FileType::class, [
                'label'    => 'Vidéo (.mp4)',
                'mapped'   => false,
                'required' => false, // IMPORTANT : pas obligatoire si déjà en base
                'constraints' => [
                    new File([
                        'maxSize'           => '256M',
                        'mimeTypes'         => ['video/mp4', 'video/quicktime', 'video/webm'],
                        'mimeTypesMessage'  => 'Formats acceptés : MP4 (recommandé), MOV, WebM.',
                    ]),
                ],
                'help' => 'MP4 H.264 conseillé, 720p suffit.',
            ])

            // Questions (cues) en JSON
            ->add('cuesJson', TextareaType::class, [
                'label'    => 'Questions (JSON)',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['rows' => 10, 'spellcheck' => 'false'],
                'help'     => 'Tableau de questions. Exemple minimal fourni dans le panneau d’infos.',
                'data'     => isset($cfg['cues'])
                    ? json_encode($cfg['cues'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
                    : "[\n  {\n    \"time\": 12,\n    \"question\": \"Mot à l'écran ?\",\n    \"options\": [{\"id\":\"A\",\"label\":\"ALPHA\"},{\"id\":\"B\",\"label\":\"BETA\"}],\n    \"answer\": \"B\",\n    \"feedbackOk\": \"Exact !\",\n    \"feedbackKo\": \"Regarde bien.\"\n  }\n]",
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
                'help'     => 'Fragment ou mot-clé révélé après la vidéo.',
            ])

            ->add('hintsJson', TextareaType::class, [
                'label'    => 'Indices (JSON)',
                'mapped'   => false,
                'required' => true, // on force à fournir quelque chose
                'attr'     => [
                    'rows' => 3,
                    'placeholder' => '["Indice 1","Indice 2"]',
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
