<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class AddPostEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('periodicity', HiddenType::class, [
            //'choices'  => array(
            //'jamais' =>0,
            //'tous les jours' => 1,
            //'toutes les semaines' => 2,
            //'tous les mois' => 3,
            //'tous les ans' => 4,
            //'personnaliser' => 5),
             'mapped' => false
            ])

            ->add('numberrepete', IntegerType::class, [
                'mapped' => false, 
                'required' =>false,
                'attr'=>[
                    'value'=>1,
                    'min'=>1,
                    'max'=>31
                ]
            ])

            ->add('typerepete', ChoiceType::class, [
                'choices'=>array(
                    'jour'=>1,
                    'semains'=>2,
                    'mois'=>3,
                    'année'=>4),                    
            'mapped' => false
             ])

            ->add('dateStartPeriod', DateType::class, [
            'input' => 'datetime',
            'widget' =>'choice',
            'mapped' => false
            ]) 

            ->add('heuredebutone', TimeType::class, array(
                'input' => 'datetime',
                'widget' =>'choice',
                'mapped' => false))

            ->add('heurefinone', TimeType::class, array(
                'input' => 'datetime',
                'widget' =>'choice',
                'mapped' => false)) 

            ->add('dateStartPeriodone', DateType::class, [
            'input' => 'datetime',
            'widget' =>'choice',
            'mapped' => false
            ]) 

            ->add('heuredebut', TimeType::class, array(
                'input' => 'datetime',
                'widget' =>'choice',
                'mapped' => false))

            ->add('heurefin', TimeType::class, array(
                'input' => 'datetime',
                'widget' =>'choice',
                'mapped' => false))   

            /*

            ->add('callback', ChoiceType::class, [
                'choices'=>array(
                    '10 minutes avant'=>0,
                    '15 minutes avant'=>1,
                    '30 minutes avant'=>2,
                    '1 heure avant'=>3,
                    '1 heures avant'=>4,
                    '24 heures avant'=>5,
                    '2 jours avant'=>5,
                    '1 semaine avant'=>6),
            'expanded'=>true,
            'multiple'=>false,
            'required' => false,
            'mapped' => false
            ])

            */
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
            /*
            ->add('daymonth', ChoiceType::class, [
                 'choices'=>array(
                    'le même jour'=>0,
                    'un autre jour dans la semaine'=>1),                    
            'multiple'=>false,
            'required' => false,
            'mapped' => false
            ])
            */
             ->add('selectdaymonth', ChoiceType::class, [
                 'choices'=>array(
                    'lun'=>0,
                    'mar'=>1,
                    'mer'=>2,
                    'jeu'=>3,
                    'ven'=>4,
                    'sam'=>5,
                    'dim'=>6),
            'expanded'=>true,
            'multiple'=>true,
            'required' => false,
            'mapped' => false
            ])

            ->add('alongchoice', ChoiceType::class,[
                 'choices'=>array(
                    'pour toujours'=>0,
                    'jusqu\'au ....'=>1),                    
            'multiple'=>false,
            'required' => false,
            'mapped' => false
            ])

            ->add('dateEndPeriod', DateType::class, [
            'input' => 'datetime',
            'widget' =>'choice',
            'mapped' => false
            ]);     
    }

    public function getParent(): string
    {
        return PostEventType::class;
    }
}
