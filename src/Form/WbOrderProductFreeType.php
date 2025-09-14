<?php

namespace App\Form;

use App\Entity\Admin\WbOrderProducts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WbOrderProductFreeType extends AbstractType
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
            ->add('subscription', SubscriptionType::class)
            ->add('description',TextType::class, array(
                'label' => 'mention complementaire :',
                'required' => false
                ))
            ->add('save',      SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WbOrderProducts::class,
        ]);
    }
}
