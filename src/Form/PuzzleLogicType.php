<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PuzzleLogicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Sécuriser $cfg en array
        $cfg = $options['config'] ?? [];
        if (is_object($cfg) && method_exists($cfg, 'getConfig')) { $cfg = $cfg->getConfig() ?? []; }
        if (!is_array($cfg)) { $cfg = []; }

        // On édite en "format simple" : un tableau de questions []
        $questions = [];
        if (isset($cfg['questions']) && is_array($cfg['questions'])) {
            $questions = $cfg['questions'];
        }

        $builder
            ->add('title', TextType::class, [
                'label'=>'Titre de l’épreuve', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['title'] ?? null,
            ])
            ->add('prompt', TextType::class, [
                'label'=>'Consigne', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['prompt'] ?? null,
            ])

            ->add('questionsJson', TextareaType::class, [
                'label'    => 'Questions (JSON)',
                'mapped'   => false,
                'required' => true,
                'attr'     => ['rows'=>8, 'spellcheck'=>'false', 'class'=>'mono'],
                'help'     => 'Tableau JSON. Chaque entrée: { "label": "...", "options":[{"id":"A","label":"..."}], "solution":{"must":["A"],"mustNot":["B"]} }',
                'data'     => isset($cfg['questions'])
                    ? json_encode($cfg['questions'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
                    : "[\n  {\n    \"label\": \"Coche le vrai\",\n    \"options\": [\n      {\"id\":\"A\",\"label\":\"2+2=4\"},\n      {\"id\":\"B\",\"label\":\"2+2=5\"}\n    ],\n    \"solution\": {\"must\":[\"A\"],\"mustNot\":[\"B\"]}\n  }\n]",
                'constraints' => [
                    new Assert\NotBlank(message: 'Colle au moins une question au format JSON.'),
                    new Assert\Callback(function($value, ExecutionContextInterface $ctx){
                        $data = json_decode((string)$value, true);
                        if (!is_array($data) || empty($data)) {
                            $ctx->buildViolation('Le JSON doit être un tableau non vide.')->addViolation();
                            return;
                        }
                        $ok = false;
                        foreach ($data as $q) {
                            if (!is_array($q)) continue;
                            if (empty(trim((string)($q['label'] ?? '')))) continue;
                            if (!is_array($q['options'] ?? null) || count($q['options'])<1) continue;
                            $ok = true; break;
                        }
                        if (!$ok) $ctx->buildViolation('Au moins une question avec label et options est requise.')->addViolation();
                    }),
                ],
            ])

            ->add('okMessage', TextType::class, [
                'label'=>'Message de réussite', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['okMessage'] ?? 'Bravo !',
            ])
            ->add('failMessage', TextType::class, [
                'label'=>'Message en cas d’erreur', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['failMessage'] ?? 'Réessaie.',
            ])
            ->add('finalClue', TextareaType::class, [
                'label'    => 'Indice final',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['rows' => 2],
                'data'     => $cfg['finalClue'] ?? '',
                'help'     => 'Texte affiché dans l’ultime énigme pour reconstituer le message secret.',
            ])

            ->add('hintsJson', TextareaType::class, [
                'label'    => 'Indices',
                'mapped'   => false,
                'required' => true, // on force à fournir quelque chose
                'attr'     => [
                    'rows' => 3,
                    'placeholder' => '["Indice 1","Indice 2"]',
                    'spellcheck' => 'false'
                ],
                'help'     => 'Ajoute 1 à 3 indices courts. Ils seront révélés aux joueurs en cas de besoin (au moins un indice requis).',
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
        $resolver->setDefaults([ 'data_class'=>null, 'config'=>[] ]);
    }
}
