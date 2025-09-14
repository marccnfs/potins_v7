<?php

namespace App\Form;

use App\Entity\UserMap\Taguery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagueryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('associatekey')
            ->add('phylo')
            ->add('bulles')
            ->add('postevents')
            ->add('dispatch')
            ->add('template')
            ->add('postations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Taguery::class,
        ]);
    }
}
