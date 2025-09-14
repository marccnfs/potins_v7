<?php

namespace App\Form;

use App\Entity\Boards\Board;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminWebsiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('namewebsite', TextType::class, array(
                'label' => 'Nom de votre spaceweb(sociéte, association, marque...',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[70]] span12'
                )
            ))
            ->add('template',TemplateAllType::class )
            ->add('url',TextType::class, array(
                'label' => 'adresse de votre site web',
                'attr' => array(
                    'class' => 'validate[minSize[3], maxSize[100]] span12'
                ),
                'required'=>false,
            ))

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Board::class,
        ]);
    }
}
