<?php

namespace App\Form;

use App\Entity\Food\CatFormule;
use App\Entity\Food\Declinaison;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormuleMenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*
           ->add('name', TextType::class, [
               'label'    => 'Nom de la formule',
           ])
            */
            ->add('prix',MoneyType::class, [
                'label'    => 'Prix',
                'html5' => false,
                'required' => false,
                'attr'  =>  array('maxlength' => 10, 'placeholder' => '0.00',  'data-thousands'=>'€')
            ])
           // ->add('description')
            ->add('boisson',CheckboxType::class, [
               'label'    => 'si une boisson au choix',
               'required' => false,
               'row_attr'=>['class'=>'choice-check']
           ])
            ->add('declinaison', EntityType::class, [
                 'class'=>Declinaison::class,
                 'choice_label'=>'name',
                 'multiple'=>false,
                 'expanded'=>true,
                 'row_attr'=>['class'=>'list-check']
             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatFormule::class,
        ]);
    }
}
