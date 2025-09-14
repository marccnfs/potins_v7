<?php

namespace App\Form;

use App\Entity\Admin\OrderProducts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class,[
                'class'=>'App\Entity\Admin\Products',
                'choice_label'=>'name',
                'multiple'=>false,
            ])
            ->add('multiple', IntegerType::class,[
                'label'=>'quantité :'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderProducts::class,
        ]);
    }
}
