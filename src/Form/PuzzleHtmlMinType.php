<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PuzzleHtmlMinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        // Sécuriser $cfg en array
        $cfg = $options['config'] ?? [];
        if (is_object($cfg) && method_exists($cfg, 'getConfig')) { $cfg = $cfg->getConfig() ?? []; }
        if (!is_array($cfg)) { $cfg = []; }

        $b
            ->add('title', TextType::class, [
                'label'=>'Titre de l’épreuve', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['title'] ?? null,
            ])
            ->add('prompt', TextType::class, [
                'label'=>'Consigne', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['prompt'] ?? null,
            ])

            ->add('starterHtml', TextareaType::class, [
                'label'=>'Code de départ (HTML)', 'mapped'=>false, 'required'=>false,
                'attr'=>['rows'=>8, 'spellcheck'=>'false'],
                'data'=> $cfg['starterHtml'] ?? "<h1>Titre</h1>\n<p>Paragraphe</p>",
            ])

            ->add('checksJson', TextareaType::class, [
                'label'=>'Règles (JSON)', 'mapped'=>false, 'required'=>false,
                'attr'=>['rows'=>10, 'spellcheck'=>'false'],
                'help'=>'Règles: selectorExists, textIncludes, selectorCountAtLeast, attrEquals, htmlIncludes.',
                'data'=> isset($cfg['checks'])
                    ? json_encode($cfg['checks'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
                    : "[\n  {\"type\":\"selectorExists\",\"selector\":\"h1\"},\n  {\"type\":\"textIncludes\",\"selector\":\"h1\",\"text\":\"SECRET\"}\n]",
            ])

            ->add('okMessage', TextType::class, [
                'label'=>'Message de réussite', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['okMessage'] ?? 'Bravo !',
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
        $resolver->setDefaults([ 'data_class'=>null, 'config'=>[] ]);
    }
}
