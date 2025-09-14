<?php

namespace App\Form;

use App\Entity\Module\Contactation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactOneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function ($options, FormEvent $event) {
            $form = $event->getForm();
            if (!$options['user_specify']) {
                $form->add('nom', TextType::class,[
                    'mapped'=>false,
                ]);
                $form->add('prenom', TextType::class,[
                    'mapped'=>false,
                ]);
                $form->add('adresse mail', EmailType::class,[
                    'mapped'=>false,
                ]);
                $form->add('telephone', TextType::class,[
                    'mapped'=>false,
                ]);
            };
        });

        $builder
            ->add('energies', ChoiceType::class, array(
                'choices'  =>['bois', 'granulé'],
                'multiple' => false,
                'expanded' => true,
                'mapped' => false
            ))

            ->add('subject', ChoiceType::class, array(
                'choices' => array(
                    "commerciale"=>"commercial",
                    "sav"=> "sav",
                    "autres"=>'autres',
                ),
            ))
            ->add('objet', ChoiceType::class, array(
                'choices' => array(
                    "commerciale"=>"commercial",
                    "sav"=> "sav",
                    "autres"=>'autres',
                ),
            ))

            ->add('commentaire', TextareaType::class, array(
                'label' => 'votre message',
                'mapped' => false,
                'required' => true
            ))

            ->add('save', SubmitType::class, array(
                'label' => 'envoyer'));

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contactation::class,
            'user_specify'=> false,
            'name_user',
        ]);

        $resolver->setAllowedTypes('user_specify', 'bool');
    }
}
