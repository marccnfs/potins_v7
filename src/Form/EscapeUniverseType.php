<?php

namespace App\Form;
use App\Entity\Games\EscapeGame;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType; // pour deleteIds si tu veux en per-item
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EscapeUniverseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        /** @var EscapeGame $eg */
        $eg = $options['eg'];
        $universe = is_array($eg->getUniverse()) ? $eg->getUniverse() : [];
        $finale = is_array($universe['finale'] ?? null) ? $universe['finale'] : [];
        $stepTitles = $eg->getTitresEtapes() ?? [1=>'',2=>'',3=>'',4=>'',5=>'',6=>''];

        $b
            ->add('title', TextType::class, [
                'mapped'=>false, 'required'=>true,
                'data'=>$eg->getTitle(), 'label'=>"Titre de l’escape game",
            ])
            ->add('difficulty', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'placeholder' => '— Niveau de difficulté —',
                'choices' => [
                    'Facile' => 'easy',
                    'Moyenne' => 'medium',
                    'Difficile' => 'hard',
                ],
                'data' => $eg->getDifficulty(),
                'label' => 'Difficulté',
                'help' => 'Indique à tes joueurs si l’escape est plutôt accessible ou corsé.',
            ])
            ->add('durationMinutes', IntegerType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Durée indicative (minutes)',
                'data' => $eg->getDurationMinutes(),
                'attr' => ['min' => 5, 'max' => 240, 'step' => 5],
                'empty_data' => '',
                'help' => 'Temps moyen pour terminer l’escape. Utilisé pour les filtres du catalogue.',
            ])
            ->add('context', TextareaType::class, [
                'mapped'=>false, 'required'=>false, 'attr'=>['rows'=>4],
                'data'=>$universe['contexte'] ?? '', 'label'=>'Contexte / histoire',
            ])
            ->add('goal', TextType::class, [
                'mapped'=>false, 'required'=>false,
                'data'=>$universe['objectif'] ?? '', 'label'=>'Objectif du joueur',
            ])
            ->add('howto', TextareaType::class, [
                'mapped'=>false, 'required'=>false, 'attr'=>['rows'=>3],
                'data'=>$universe['modeEmploi'] ?? '', 'label'=>'Mode d’emploi (facultatif)',
            ])
            ->add('finalPrompt', TextareaType::class, [
                'mapped'=>false, 'required'=>false, 'attr'=>['rows'=>3],
                'data'=>$finale['prompt'] ?? '',
                'label'=>'Introduction de la révélation finale',
                'help'=>'Texte affiché avant la reconstitution des fragments.',
            ])
            ->add('finalReveal', TextareaType::class, [
                'mapped'=>false, 'required'=>false, 'attr'=>['rows'=>3],
                'data'=>$finale['reveal'] ?? '',
                'label'=>'Message final révélé',
                'help'=>'Message ou récompense affiché une fois les fragments remis dans l’ordre.',
            ])
            ->add('guide', ChoiceType::class, [
                'mapped'=>false, 'required'=>false, 'expanded'=>false,
                'choices'=>[
                    '🤖 Robot'=>'Robot',
                    '🕵️ Espion'=>'Espion',
                    '👻 Fantôme'=>'Fantôme',
                    '🔬 Scientifique'=>'Scientifique',
                ],
                'data'=>$universe['guide'] ?? null,
                'placeholder'=>'— Choisir un guide —',
                'label'=>'Personnage guide',
            ])

            // Titres des 6 étapes : un petit tableau 1..6
            ->add('stepTitles', CollectionType::class, [
                'mapped'=>false, 'required'=>false,
                'allow_add'=>false, 'allow_delete'=>false,
                'entry_type'=>TextType::class,
                'entry_options'=>['required'=>false],
                'data'=>[
                    1 => $stepTitles[1] ?? '',
                    2 => $stepTitles[2] ?? '',
                    3 => $stepTitles[3] ?? '',
                    4 => $stepTitles[4] ?? '',
                    5 => $stepTitles[5] ?? '',
                    6 => $stepTitles[6] ?? '',
                ],
                'label'=>'Titres personnalisés des étapes (facultatif)',
            ])

            // Ajout d’images : multiple, unmapped (on crée les Illustration())
            ->add('newImages', FileType::class, [
                'mapped'=>false, 'required'=>false, 'multiple'=>true,
                'label'=>'Ajouter des images (JPG/PNG/WebP)',
                'attr'=>['accept'=>'image/*'],
            ])

            // Suppression d’images (on propose les existantes)
            ->add('deleteImages', ChoiceType::class, [
                'mapped'=>false, 'required'=>false, 'multiple'=>true, 'expanded'=>true,
                'label'=>'Supprimer des images existantes',
                'choices'=>array_combine(
                    array_map(fn($i)=> sprintf('#%d — %s', $i->getId(), method_exists($i,'getOriginalName') ? $i->getOriginalName() : 'illustration'), $eg->getIllustrations()->toArray()),
                    array_map(fn($i)=> $i->getId(), $eg->getIllustrations()->toArray())
                ),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([ 'data_class'=>null, 'eg'=>null ]);
    }
}
