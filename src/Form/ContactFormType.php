<?php


namespace App\Form;


use App\Entity\LogMessages\MsgW;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContactFormType extends AbstractType
{

    private $security;


    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('subject', TextType::class)
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
            'data_class' => MsgW::class,
        ]);
    }
}