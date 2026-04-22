<?php

namespace App\Form;

use App\Entity\Equipement;
use App\Entity\Review;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Si user_equipements est défini, on restreint le choix à ces équipements
        $userEquipements = $options['user_equipements'];

        $equipementOptions = [
            'class'        => Equipement::class,
            'choice_label' => 'nom',
            'label'        => 'Équipement concerné',
            'attr'         => ['class' => 'form-control'],
            'placeholder'  => 'Sélectionnez un équipement',
        ];

        if ($userEquipements !== null) {
            $equipementOptions['choices'] = $userEquipements;
        }

        $builder
            ->add('equipement', EntityType::class, $equipementOptions)
            ->add('note', ChoiceType::class, [
                'label'   => 'Note (sur 5)',
                'choices' => [
                    '5 Étoiles - Excellent'   => 5,
                    '4 Étoiles - Très bien'   => 4,
                    '3 Étoiles - Bien'        => 3,
                    '2 Étoiles - Passable'    => 2,
                    '1 Étoile  - Mauvais'     => 1,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('commentaire', TextareaType::class, [
                'label'    => 'Vos commentaires',
                'attr'     => [
                    'class'       => 'form-control',
                    'rows'        => 5,
                    'placeholder' => 'Décrivez votre expérience vis-à-vis de cet équipement...',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'       => Review::class,
            'user_equipements' => null, // null = tous les équipements (admin) ; array = restreint (user)
        ]);
    }
}
