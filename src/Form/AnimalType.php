<?php

namespace App\Form;

use App\Entity\Animal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('espece', TextType::class, ['label' => 'Espèce'])
            ->add('race', TextType::class, ['label' => 'Race', 'required' => false])
            ->add('poids', NumberType::class, ['label' => 'Poids (kg)', 'required' => false, 'scale' => 2])
            ->add('etatSante', ChoiceType::class, [
                'label' => 'État de santé',
                'required' => false,
                'choices' => ['Sain' => 'Sain', 'Malade' => 'Malade', 'En récupération' => 'En récupération', 'Gestation' => 'Gestation'],
                'placeholder' => 'Sélectionner l\'état',
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Photo',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer la photo',
                'download_uri' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
            'attr' => ['novalidate' => true],
        ]);
    }
}
