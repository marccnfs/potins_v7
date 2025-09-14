<?php

namespace App\Form;

use App\Entity\Admin\Wborders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorderInscriptionPotinsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        foreach ($options['listdate'] as $key=>$date){
            $builder->add($key, CheckboxType::class,array(
                'label'=>$date->format('j/m/Y'),
                'mapped'=>false,
                'required'=>false
            ));
        }

        $builder->add('save', SubmitType::class,[
                'attr'=>['class'=>'btn-send'],
                'label'=>'enregistrez'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wborders::class,
        ]);

        $resolver->setDefaults([
            'listdate'=>[],
        ]);
    }
}
