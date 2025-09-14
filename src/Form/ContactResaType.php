<?php

namespace App\Form;

use App\Entity\Module\Contactation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject',TextType::class, array(
                'label' => 'Référence du module',
                'required' => true))
            ->add('description',TextType::class, array(
                'label' => 'Objet du contact',
                'required' => true,
                'mapped'=>false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contactation::class,
        ]);
    }
}
