<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('nom')->add('description')->add('prix')->add('disponible')->add('image', MediaType::class)->add('tva')->add('categorie');
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ben\EcommerceBundle\Entity\Produits'
        ));
    }


    public function getBlockPrefix(): string
    {
        return 'ben_ecommercebundle_produits';
    }


}
