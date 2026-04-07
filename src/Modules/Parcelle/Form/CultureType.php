<?php

namespace App\Modules\Parcelle\Form;

use App\Modules\Parcelle\Entity\Culture;
use App\Modules\Parcelle\Entity\Parcelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CultureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la culture',
                'attr' => ['placeholder' => 'Ex: Blé'],
            ])
            ->add('type_culture', TextType::class, [
                'label' => 'Type de culture',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Céréale'],
            ])
            ->add('date_plantation', DateType::class, [
                'label' => 'Date de plantation',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('date_recolte_prevue', DateType::class, [
                'label' => 'Date de récolte prévue',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('etat_culture', TextType::class, [
                'label' => 'État de la culture',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: En croissance'],
            ])
            ->add('parcelle', EntityType::class, [
                'class' => Parcelle::class,
                'choice_label' => 'nom', // or whatever field
                'label' => 'Parcelle',
                'placeholder' => 'Sélectionner une parcelle',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Culture::class,
        ]);
    }
}