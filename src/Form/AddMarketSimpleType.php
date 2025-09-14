<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class AddMarketSimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             
            ->add('heuredebut', TimeType::class, array(
                'input' => 'datetime',
                'widget' =>'choice',
                'mapped' => false))

            ->add('heurefin', TimeType::class, array(
                'input' => 'datetime',
                'widget' =>'choice',
                'mapped' => false))           

            ->add('periodicity', ChoiceType::class, [
            'choices'  => array(
            'jamais' =>0,
            'tous les jours' => 1,
            'toutes les semaines' => 2,
            'tous les mois' => 3,
            'tous les ans' => 4,
            'personnaliser' => 5),
             'mapped' => false
            ])

            ->add('numberrepete', IntegerType::class, [
                'mapped' => false, 
                'required' =>false
            ])

            ->add('typerepete', ChoiceType::class, [
                'choices'=>array(
                    'jour'=>1,
                    'semains'=>2,
                    'mois'=>3,
                    'année'=>4),                    
            'mapped' => false
             ])

            ->add('daysweek', ChoiceType::class, [
                 'choices'=>array(
                    'lun'=>'Monday',
                    'mar'=>'Tuesday',
                    'mer'=>'Wednesday',
                    'jeu'=>'Thursday',
                    'ven'=>'Friday',
                    'sam'=>'Saturday',
                    'dim'=>'Sunday'),
            'expanded'=>true,
            'multiple'=>true,
            'required' => true,
            'mapped' => false
            ])

            ->add('daymonth', ChoiceType::class, [
                 'choices'=>array(
                    'le même jour'=>0,
                    'un autre jour dans la semaine'=>1),                    
            'multiple'=>false,
            'required' => false,
            'mapped' => false
            ])

             ->add('selectdaymonth', ChoiceType::class, [
                 'choices'=>array(
                    'lundi'=>0,
                    'mardi'=>1,
                    'mercredi'=>2,
                    'jeudi'=>3,
                    'vendredi'=>4,
                    'samedi'=>5,
                    'dimanche'=>6),
            'expanded'=>true,
            'multiple'=>false,
            'required' => false,
            'mapped' => false
            ]);

            
             
    }

    public function getParent(): string
    {
        return MarketType::class;
    }
}
