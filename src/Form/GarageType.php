<?php

namespace App\Form;

use App\Entity\Equipement;
use App\Entity\Garage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GarageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom du garage'],
                'label' => 'Nom du Garage'
            ])
            ->add('adresse', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Adresse complète'],
                'label' => 'Adresse',
                'required' => false
            ])
            ->add('latitude', \Symfony\Component\Form\Extension\Core\Type\NumberType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 36.8065'],
                'label' => 'Latitude',
                'required' => false
            ])
            ->add('longitude', \Symfony\Component\Form\Extension\Core\Type\NumberType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 10.1815'],
                'label' => 'Longitude',
                'required' => false
            ])
            ->add('capacite', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Capacité max en équipements'],
                'label' => 'Capacité',
                'required' => false
            ])
            ->add('responsable', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom du responsable'],
                'label' => 'Responsable',
                'required' => false
            ])
            ->add('telephone', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Numéro de téléphone'],
                'label' => 'Téléphone',
                'required' => false
            ])
            ->add('equipements', EntityType::class, [
                'class' => Equipement::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Assigner des Équipements',
                'attr' => ['class' => 'checkbox-group']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Garage::class,
        ]);
    }
}
