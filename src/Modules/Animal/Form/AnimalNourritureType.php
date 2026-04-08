<?php

namespace App\Modules\Animal\Form;

use App\Modules\Animal\Entity\AnimalNourriture;
use App\Modules\Animal\Entity\Nourriture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalNourritureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nourriture', EntityType::class, [
                'class' => Nourriture::class,
                'choice_label' => 'name',
                'label' => 'Food',
                'placeholder' => 'Select food',
            ])
            ->add('quantityFed', NumberType::class, ['label' => 'Quantity Fed', 'scale' => 2])
            ->add('feedingDate', DateTimeType::class, ['label' => 'Feeding Date', 'required' => false, 'widget' => 'single_text'])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AnimalNourriture::class]);
    }
}
