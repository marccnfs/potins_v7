<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalizeGpsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('codep', TextType::class, array(
                'mapped' => false
            ))
            ->add('city', HiddenType::class, array(
            'mapped' => false
            ))
            ->add('codeok', HiddenType::class, array(
                'mapped' => false
            ));
    }
}
