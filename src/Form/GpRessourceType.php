<?php

namespace App\Form;

use App\Entity\Module\GpRessources;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GpRessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*
             *  ->add('name',TextType::class, [
                 'label' => 'nom du groupe de ressource',
                 'required' => false])

            ->add('description',TextType::class, [
                 'label' => 'description',
                'required' => false])

            ->add('start',DateType::class, [
                'label' => 'date début publication',
                'widget' => 'single_text',
                'required' => true,
                'mapped'=>false])

             ->add('end',DateType::class, [
                 'label' => 'date fin publication',
                 'widget' => 'single_text',
                 'required' => true,
                 'mapped'=>false])
 */ /*
            ->add('catformules', CollectionType::class, array(
                'entry_type' => FormuleMenuType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference'=> false,
                'row_attr'=>['class'=>'tcat_posedit']

            ))

             ->add('services', EntityType::class, [
                'class'=>Service::class,
                'choice_label'=>'name',
                'multiple'=>false,
                'expanded'=>true,
                 'row_attr'=>['class'=>'list-service']
             ])
            */

            ->add('listarticle', HiddenType::class,[
                'mapped'=>false
            ])

            ->add('save', SubmitType::class,[
                'attr'=>['class'=>'btn-send'],
                'label'=>'enregistrez'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GpRessources::class,
        ]);
    }
}
