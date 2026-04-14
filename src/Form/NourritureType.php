<?php

namespace App\Form;

use App\Entity\Nourriture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NourritureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('type', TextType::class, ['label' => 'Type'])
            ->add('quantity', NumberType::class, ['label' => 'Quantity', 'scale' => 2])
            ->add('unit', TextType::class, ['label' => 'Unit', 'required' => false])
            ->add('nutritionalValue', TextType::class, ['label' => 'Nutritional Value', 'required' => false])
            ->add('expiryDate', DateTimeType::class, ['label' => 'Expiry Date', 'required' => false, 'widget' => 'single_text'])
            ->add('supplier', TextType::class, ['label' => 'Supplier', 'required' => false])
            ->add('cost', NumberType::class, ['label' => 'Cost', 'required' => false, 'scale' => 2])
            ->add('isActive', CheckboxType::class, ['label' => 'Active', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Nourriture::class,
            'attr' => ['novalidate' => true],
        ]);
    }
}
