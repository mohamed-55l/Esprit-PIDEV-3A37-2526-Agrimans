<?php

namespace App\Modules\Animal\Form;

use App\Modules\Animal\Entity\Animal;
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
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('type', TextType::class, ['label' => 'Espèce'])
            ->add('breed', TextType::class, ['label' => 'Race', 'required' => false])
            ->add('weight', NumberType::class, ['label' => 'Poids (kg)', 'required' => false, 'scale' => 2])
            ->add('healthStatus', ChoiceType::class, [
                'label' => 'État de santé',
                'required' => false,
                'choices' => ['Sain' => 'Sain', 'Malade' => 'Malade', 'En récupération' => 'En récupération', 'Gestation' => 'Gestation'],
                'placeholder' => 'Sélectionner l\'état',
            ])
            ->add('userId', IntegerType::class, ['label' => 'ID Utilisateur', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
            'attr' => ['novalidate' => true],
        ]);
    }
}
