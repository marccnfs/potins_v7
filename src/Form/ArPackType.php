<?php

namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Games\ArPack;

class ArPackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du pack (sans espace)',
            ])
            ->add('images', FileType::class, [
                'label' => 'Images à inclure (.jpg / .png)',
                'multiple' => true,
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '8M',
                                'mimeTypes' => ['image/jpeg', 'image/png'],
                                'mimeTypesMessage' => 'Formats autorisés : .jpg, .png',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArPack::class,
        ]);
    }
}
