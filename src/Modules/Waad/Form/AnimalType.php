<?php

namespace App\Modules\Waad\Form;

use App\Modules\Waad\Entity\Animal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('type', TextType::class, ['label' => 'Type'])
            ->add('breed', TextType::class, ['label' => 'Breed', 'required' => false])
            ->add('age', IntegerType::class, ['label' => 'Age', 'required' => false])
            ->add('weight', NumberType::class, ['label' => 'Weight (kg)', 'required' => false, 'scale' => 2])
            ->add('healthStatus', ChoiceType::class, [
                'label' => 'Health Status',
                'required' => false,
                'choices' => ['Healthy' => 'healthy', 'Sick' => 'sick', 'Recovering' => 'recovering'],
                'placeholder' => 'Select status',
            ])
            ->add('isActive', CheckboxType::class, ['label' => 'Active', 'required' => false])
            ->add('productionType', ChoiceType::class, [
                'label' => 'Production Type',
                'required' => false,
                'choices' => ['Milk' => 'milk', 'Meat' => 'meat', 'Eggs' => 'eggs', 'Wool' => 'wool'],
                'placeholder' => 'Select type',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Animal::class]);
    }
}
