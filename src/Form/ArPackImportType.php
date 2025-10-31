<?php

namespace App\Form;

use App\Entity\Games\ArPack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArPackImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du pack',
            ])
            ->add('mindFile', FileType::class, [
                'label' => 'Fichier MindAR (.mind)',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'mimeTypesMessage' => 'Sélectionnez un fichier .mind valide',
                    ]),
                ],
            ])
            ->add('jsonFile', FileType::class, [
                'label' => 'Fichier JSON (optionnel)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Image de prévisualisation (optionnel)',
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ArPack::class]);
    }
}
