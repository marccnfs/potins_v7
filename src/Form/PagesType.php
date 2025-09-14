<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagesType extends AbstractType
{
        public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       // $builder->add('titre')->add('contenu');
        $builder
            ->add('titre',TextType::class)
            ->add('contenu',null, array('attr' => array('class' => 'ckeditor')));

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ben\PagesBundle\Entity\Pages'
        ));
    }


    public function getBlockPrefix(): string
    {
        return 'ben_pagesbundle_pages';
    }


}
