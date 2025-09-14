<?php

namespace App\Form;

use App\Entity\Admin\WbOrderProducts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WbOrderProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
