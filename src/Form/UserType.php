<?php

namespace App\Form;

use App\Entity\Users;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('full_name', null, [
                'label' => 'Nom complet'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('phone', null, [
                'required' => false,
                'label' => 'Téléphone'
            ])
            ->add('password_hash', PasswordType::class, [
                'label' => 'Mot de passe'
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => UserRole::USER,
                    'Admin' => UserRole::ADMIN,
                ],
                'label' => 'Rôle'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
