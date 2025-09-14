<?php

namespace App\Form;

use App\Entity\Sector\Sectors;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse', CollectionType::class,[
                'label'=>"Liste adresse :",
                'entry_type'=>AddressType::class,
                'allow_add'=>true,
                'allow_delete'=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sectors::class,
        ]);
    }
}
