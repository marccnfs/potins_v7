<?php

namespace App\Form;


use App\Entity\Users\Registered;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisteredonlyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sex', ChoiceType::class, [
                'choices'=>array(
                    'Mme'=>1,
                    'Mr'=>2),
                'label' => 'Mme, Mr...'
            ])
            ->add('firstname', TextType::class, array(
                'label' => 'Nom',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'Prénom',
                'attr' => array(
                    'class' => 'validate[required, minSize[3], maxSize[150]] span12'
                )
            ))
            ->add('birthdate', DateType::class,[
                'widget' => 'single_text',
                'label' => 'Date de naissance',
                // this is actually the default format for single_text
                //'format' => 'yyyy',
                //'input'  => 'datetime',
                'required'=>false
            ])
            ->add('save',  SubmitType::class,[
                'attr'=>['class'=>'btn-send'],
                'label'=>'enregistrez'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Registered::class,
        ]);
    }
}
