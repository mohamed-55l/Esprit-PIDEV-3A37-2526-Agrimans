<?php

namespace App\Modules\Equipement\Form;

use App\Modules\Equipement\Entity\Equipement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'équipement',
                'attr' => ['placeholder' => 'Ex: Tracteur Landini'],
            ])
            ->add('type', TextType::class, [
                'label' => 'Type/Catégorie',
                'attr' => ['placeholder' => 'Ex: Tracteur, Pompe...'],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr' => ['placeholder' => 'Prix en Dinars'],
            ])
            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponibilité',
                'choices' => [
                    'Disponible' => 'Disponible',
                    'Indisponible' => 'Indisponible',
                    'En maintenance' => 'En maintenance',
                ],
                'placeholder' => 'Sélectionnez un statut',
            ])
            // On ne met pas (on cache) le champ userId côté frontend car il sera
            // normalement géré par l'authentification (l'ID de la personne connectée).
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipement::class,
        ]);
    }
}
