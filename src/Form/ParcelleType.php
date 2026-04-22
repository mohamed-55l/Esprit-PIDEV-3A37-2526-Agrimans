<?php

namespace App\Form;

use App\Entity\Parcelle;
use App\Entity\Users;
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
                'label'    => 'Nom de la parcelle',
                'required' => true,
                'attr'     => ['placeholder' => 'Ex: Parcelle A', 'class' => 'form-control'],
            ])
            ->add('superficie', NumberType::class, [
                'label'    => 'Superficie (en hectares)',
                'required' => true,
                'attr'     => ['placeholder' => 'Ex: 5.5', 'class' => 'form-control'],
            ])
            ->add('localisation', TextType::class, [
                'label'    => 'Localisation',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: Tunis, Ariana', 'class' => 'form-control'],
            ])
            ->add('type_sol', TextType::class, [
                'label'    => 'Type de sol',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: Argileux, Sableux', 'class' => 'form-control'],
            ])
            ->add('latitude', NumberType::class, [
                'label'    => 'Latitude',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: 36.8065', 'class' => 'form-control'],
            ])
            ->add('longitude', NumberType::class, [
                'label'    => 'Longitude',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: 10.1815', 'class' => 'form-control'],
            ]);

        // Le champ user est affiché UNIQUEMENT pour les admins
        if ($options['show_user_field']) {
            $builder->add('user', EntityType::class, [
                'class'        => Users::class,
                'choice_label' => function (Users $user) {
                    return $user->getEmail() . ' (' . $user->getFullName() . ')';
                },
                'label'       => 'Assigner à l\'utilisateur',
                'required'    => true,
                'placeholder' => '— Choisir un utilisateur —',
                'attr'        => ['class' => 'form-control'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Parcelle::class,
            'show_user_field' => false,   // false par défaut (comportement user normal)
        ]);
    }
}
