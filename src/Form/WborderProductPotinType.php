<?php

namespace App\Form;

use App\Entity\Admin\OrderProducts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WborderProductPotinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('registered', RegisteredType::class, array(
                'label' => false,
                'attr' => array('class' => 'block_form_row_iner')
            ))

            /*
            ->add('description',TextType::class, array(
                'label' => 'Observations',
                'required' => false
                ))

            ->add('save',      SubmitType::class);
             */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderProducts::class,
        ]);
    }
}
