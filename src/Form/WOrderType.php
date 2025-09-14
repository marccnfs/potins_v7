<?php

namespace App\Form;

use App\Entity\Admin\Wborders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class,array(
                'widget' => 'single_text',
            ))
            ->add('products', CollectionType::class,array(
                'entry_type'=> WbOrderProductFreeType::class,
                'allow_add'=> true,
                'allow_delete'=> true
            ))
            ->add('save',      SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wborders::class,
        ]);
    }
}
