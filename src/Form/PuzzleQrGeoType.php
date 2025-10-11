<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PuzzleQrGeoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Sécuriser $cfg en array
        $cfg = $options['config'] ?? [];
        if (is_object($cfg) && method_exists($cfg, 'getConfig')) { $cfg = $cfg->getConfig() ?? []; }
        if (!is_array($cfg)) { $cfg = []; }

        $target = is_array($cfg['target'] ?? null) ? $cfg['target'] : [];
        $mode   = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
        $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];

        $builder
            ->add('title', TextType::class, [
                'label'=>'Titre de l’épreuve', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['title'] ?? null,
            ])
            ->add('prompt', TextType::class, [
                'label'=>'Consigne', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['prompt'] ?? null,
            ])
            ->add('mode', ChoiceType::class, [
                'label'   => 'Mode de validation',
                'mapped'  => false,
                'required'=> true,
                'data'    => $mode,
                'choices' => [
                    'Validation par géolocalisation (GPS)' => 'geo',
                    'Validation par QR caché (sans GPS)'   => 'qr_only',
                ],
                'help'    => 'Choisis « QR caché » si tu préfères cacher un code à scanner plutôt que d’utiliser la localisation.',
            ])
            ->add('qrValidateMessage', TextType::class, [
                'label'    => 'Message affiché après le scan',
                'mapped'   => false,
                'required' => false,
                'data'     => $qrOnly['validateMessage'] ?? 'Bravo !',
                'help'     => 'Texte montré immédiatement quand le QR caché est scanné (valide l’étape et révèle l’indice final).',
            ])
            ->add('qrNoExpiry', CheckboxType::class, [
                'label'    => 'Durée de validité illimitée',
                'mapped'   => false,
                'required' => false,
                'data'     => (bool)($qrOnly['noExpiry'] ?? false),
                'help'     => 'Si coché, le QR généré ne s’expire plus automatiquement après 15 minutes.',
            ])
            ->add('lat', NumberType::class, [
                'label'=>'Latitude', 'mapped'=>false, 'required'=>false, 'data'=>$target['lat'] ?? null,
                'scale'=>8, 'attr'=>['step'=>'any', 'placeholder'=>'48.8566'],
                'constraints'=>[ new Range(['min'=>-90, 'max'=>90, 'notInRangeMessage'=>'Latitude entre -90 et 90']) ],
            ])
            ->add('lng', NumberType::class, [
                'label'=>'Longitude', 'mapped'=>false, 'required'=>false, 'data'=>$target['lng'] ?? null,
                'scale'=>8, 'attr'=>['step'=>'any', 'placeholder'=>'2.3522'],
                'constraints'=>[ new Range(['min'=>-180, 'max'=>180, 'notInRangeMessage'=>'Longitude entre -180 et 180']) ],
            ])
            ->add('radiusMeters', IntegerType::class, [
                'label'=>'Rayon (mètres)', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['radiusMeters'] ?? 150,
                'attr'=>['min'=>10, 'max'=>50000], 'constraints'=>[ new Range(['min'=>10, 'max'=>50000]) ],
            ])

            ->add('okMessage', TextType::class, [
                'label'=>'Message OK', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['okMessage'] ?? 'Bravo !',
            ])
            ->add('denyMessage', TextType::class, [
                'label'=>'Message hors zone', 'mapped'=>false, 'required'=>false, 'data'=>$cfg['denyMessage'] ?? 'Hors zone…',
            ])
            ->add('needHttpsMessage', TextType::class, [
                'label'=>'Message HTTPS requis', 'mapped'=>false, 'required'=>false,
                'data'=>$cfg['needHttpsMessage'] ?? 'HTTPS requis pour la géolocalisation',
            ])

            ->add('finalClue', TextareaType::class, [
                'label'    => 'Indice final',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['rows' => 2],
                'data'     => $cfg['finalClue'] ?? '',
                'help'     => 'Fragment transmis après validation de cette étape.',
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
