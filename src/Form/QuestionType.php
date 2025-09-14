<?php

namespace App\Form;


use App\Entity\Quiz\Questionnaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Question',
            ])
            ->add('optionA', TextType::class, [
                'label' => 'Option A',
            ])
            ->add('optionB', TextType::class, [
                'label' => 'Option B',
            ])
            ->add('optionC', TextType::class, [
                'label' => 'Option C',
            ])
            ->add('optionD', TextType::class, [
                'label' => 'Option D',
            ])
            ->add('correctAnswer', ChoiceType::class, [
                'label' => 'Correct Answer',
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Question Type',
                'choices' => [
                    'Multiple Choice' => 'multiple_choice',
                    'True/False' => 'true_false',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Question',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
        ]);
    }
}