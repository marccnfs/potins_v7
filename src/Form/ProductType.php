<?php

namespace App\Form;

use App\Entity\Admin\Products;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('unit')
            ->add('disponible')
            ->add('remisaable')
            ->add('pict', PictType::class, array( 'label'=>' image produit :'))
            ->add('categorie')
            ->add('tva')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
