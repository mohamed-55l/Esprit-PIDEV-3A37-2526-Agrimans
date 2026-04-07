<?php

namespace App\Modules\Parcelle\Form;

use App\Modules\Parcelle\Entity\Parcelle;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la parcelle',
                'attr' => ['placeholder' => 'Ex: Parcelle A'],
            ])
            ->add('superficie', NumberType::class, [
                'label' => 'Superficie (en hectares)',
                'attr' => ['placeholder' => 'Ex: 5.5'],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Tunis, Ariana'],
            ])
            ->add('type_sol', TextType::class, [
                'label' => 'Type de sol',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Argileux, Sableux'],
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 36.8065'],
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 10.1815'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // or whatever field to display
                'label' => 'Utilisateur',
                'placeholder' => 'Sélectionner un utilisateur',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcelle::class,
        ]);
    }
}