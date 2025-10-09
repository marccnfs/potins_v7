<?php

// src/Form/ParticipantProfileType.php
namespace App\Form;


use App\Entity\Users\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ParticipantProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b
            ->add('prenom', TextType::class, [
                'label'=>'Prénom','required'=>false,
                'attr'=>['maxlength'=>80,'placeholder'=>'Ton nom affiché']
            ])
            ->add('nickname', TextType::class, [
                'label'=>'Pseudo','required'=>false,
                'attr'=>['maxlength'=>80,'placeholder'=>'Ton nom affiché']
            ])
            ->add('bio', TextareaType::class, [
                'label'=>'Bio','required'=>false,'attr'=>['rows'=>3,'placeholder'=>'Quelques mots sur toi…']
            ])
            ->add('codeAtelier', TextType::class, [
                'label'=>'Code atelier','required'=>false,
                'help'=>'Code à 4 chiffres communiqué pendant la session.',
                'attr'=>['maxlength'=>20,'placeholder'=>'Code atelier']
            ])
            ->add('codeSecret', TextType::class, [
                'label'=>'Code secret','required'=>true,
                'help'=>'Il reste privé et te permet de te reconnecter.',
                'attr'=>['maxlength'=>4,'placeholder'=>'Ton code secret']
            ])
            /*
            ->add('website', TextType::class, [
                'label'=>'Site web','required'=>false,'attr'=>['placeholder'=>'https://…']
            ])
            ->add('twitter', TextType::class, [
                'label'=>'Twitter / X','required'=>false,'attr'=>['placeholder'=>'@tonhandle ou URL']
            ])
            ->add('mastodon', TextType::class, [
                'label'=>'Mastodon','required'=>false,'attr'=>['placeholder'=>'@user@instance ou URL']
            ])
            */
            ->add('avatarFile', FileType::class, [
                'label'=>'Avatar','required'=>false,
                'mapped'=>true, // Vich s’occupe de l’upload
                'attr'=>['accept'=>'image/*']
            ])
            ->add('removeAvatar', CheckboxType::class, [
                'label'=>'Supprimer l’avatar','required'=>false,'mapped'=>false
            ])
            ->add('allowContact', CheckboxType::class, [
                'label'=>'Autoriser qu’on me contacte (ex: retours sur mes jeux)','required'=>false
            ])
            ->add('prefLang', ChoiceType::class, [
                'label'=>'Langue','required'=>false,'mapped'=>false,
                'choices'=>['Français'=>'fr','English'=>'en'],
                'data'=>($options['data']?->getPreferences()['lang'] ?? 'fr')
            ])
            ->add('prefTheme', ChoiceType::class, [
                'label'=>'Thème','required'=>false,'mapped'=>false,
                'choices'=>['Clair'=>'light','Sombre'=>'dark'],
                'data'=>($options['data']?->getPreferences()['theme'] ?? 'light')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([ 'data_class'=>Participant::class ]);
    }
}

