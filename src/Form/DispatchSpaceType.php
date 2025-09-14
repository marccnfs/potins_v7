<?php

namespace App\Form;

use App\Entity\Member\Activmember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DispatchSpaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Nom de votre spaceweb(sociéte, association, marque...',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[70]] span12'
                )
            ))
            ->add('template',TemplateAllType::class )
            ->add('website',NewWebsiteType::class )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activmember::class,
        ]);
    }
}
