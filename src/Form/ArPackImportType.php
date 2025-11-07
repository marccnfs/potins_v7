<?php

namespace App\Form;

use App\Entity\Games\ArPack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class ArPackImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du pack',
                'help' => 'Ce nom est utilisé pour organiser les fichiers sur le serveur.',
            ])
            ->add('mindFile', FileType::class, [
                'label' => 'Fichier MindAR (.mind)',
                'mapped' => false,
                'required' => $options['mind_file_required'],
                'attr' => [
                    'accept' => '.mind',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypesMessage' => 'Sélectionnez un fichier .mind valide',
                    ]),
                ],
            ])
            ->add('jsonFile', FileType::class, [
                'label' => 'Fichier JSON (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => '.json',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => ['application/json', 'text/json', 'text/plain'],
                        'mimeTypesMessage' => 'Sélectionnez un fichier JSON valide',
                    ]),
                ],
            ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Image de prévisualisation (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Formats autorisés : .jpg, .png, .gif',
                    ]),
                ],
            ])
            ->add('modelFiles', FileType::class, [
                'label' => 'Ressources 3D / média (optionnel)',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'help' => 'Ajoutez des fichiers .glb, .mp4/.webm ou des images complémentaires.',
                'attr' => [
                    'accept' => '.glb,.gltf,.json,model/gltf-binary,model/gltf+json,application/json,video/mp4,video/webm,image/jpeg,image/png,image/gif',
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '64M',
                                'mimeTypes' => [
                                    'model/gltf-binary',
                                    'model/gltf+json',
                                    'application/octet-stream',
                                    'application/json',
                                    'text/json',
                                    'video/mp4',
                                    'video/webm',
                                    'video/ogg',
                                    'video/quicktime',
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                ],
                                'mimeTypesMessage' => 'Formats autorisés : .glb, .gltf, .json, .mp4, .webm, .jpg, .png, .gif',
                            ]),
                        ],
                    ]),
                ],
            ])
            ->add('targetImages', FileType::class, [
                'label' => 'Images cibles (.jpg)',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'help' => 'Ajoutez les images MindAR générées pour ce pack (format .jpg).',
                'attr' => [
                    'accept' => '.jpg,.jpeg,image/jpeg',
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '12M',
                                'mimeTypes' => ['image/jpeg'],
                                'mimeTypesMessage' => 'Téléversez uniquement des images .jpg/.jpeg',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArPack::class,
            'mind_file_required' => true,
        ]);
        $resolver->setAllowedTypes('mind_file_required', 'bool');
    }
}
