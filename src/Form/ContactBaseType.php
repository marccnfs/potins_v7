<?php


namespace App\Form;


use App\Entity\Comments\CommentNotice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class ContactBaseType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * ContactBaseType constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $this->security->getUser();
            $form = $event->getForm();
            if (!$user) {
                $form->add('username', TextType::class, array(
                    'label' => 'votre nom',
                    'attr'=>array(
                        'data-rule'=>'minlen:4',
                        'data-msg'=>'4 caractères minimum',
                        'class' => 'validate[required, minSize[3], maxSize[150]] span12'),
                    'mapped' => false,
                    'required' => true,
                ))
                    ->add('email', EmailType::class, array(
                        'label' => 'votre email',
                        'required' => true,
                        'attr'=>array('data-rule'=>'email', 'data-msg'=>'adresse mail invalide'),
                        'mapped' => false
                    ))

                    ->add('telephone', PhoneNumberType::class, array(
                        'default_region' => 'FR', 'format' => PhoneNumberFormat::NATIONAL,
                        'label' => 'telephone (mobile)',
                        'attr'=>array('data-rule'=>'minlen:4', 'data-msg'=>'numero invalide'),
                        'required' => false,
                        'mapped' => false
                    ));
            }
        });

        $builder->add('content', TextareaType::class, array(
                'label' => 'votre message',
            'attr'=>array('data-rule'=>'minlen:10', 'data-msg'=>'Merci de mettre un contenu de 10 caractères minimum'),
                'mapped' => false,
                'required' => true
            ))

        ->add('contact', HiddenType::class, [
        'mapped' => false
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommentNotice::class,
        ]);
    }
}
