<?php


namespace App\Form;


use App\Entity\LogMessages\PrivateConvers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PrivateConversFormType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, array(
            'label' => 'votre message',
            'attr'=>array('data-rule'=>'minlen:10', 'data-msg'=>'Merci de mettre un contenu de 10 caractères minimum'),
            'mapped' => false,
            'required' => true
            ))
            ->add('username', HiddenType::class, [
                'mapped' => false
            ])
            ->add('email', HiddenType::class, [
                'mapped' => false
            ])
            ->add('type', HiddenType::class, [
                'mapped' => false
            ])
            ->add('id', HiddenType::class, [
                'mapped' => false
            ])
            ->add('follow', HiddenType::class, [
            'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PrivateConvers::class,
        ]);
    }
}