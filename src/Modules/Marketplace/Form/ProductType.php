<?php

namespace App\Modules\Marketplace\Form;

use App\Modules\Marketplace\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: Tomates Bio'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Décrivez votre produit...', 'rows' => 3],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (DT/kg)',
                'attr' => ['placeholder' => 'Prix par kg'],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité (kg)',
                'attr' => ['placeholder' => 'Quantité disponible en kg'],
                'html5' => true,
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Légumes' => 'VEGETABLES',
                    'Fruits' => 'FRUITS',
                    'Céréales' => 'GRAINS',
                    'Foin' => 'HAY',
                ],
                'placeholder' => 'Sélectionnez une catégorie',
            ])
            ->add('supplier', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false,
                'attr' => ['placeholder' => 'Nom du fournisseur'],
            ])
            ->add('expiryDate', DateType::class, [
                'label' => "Date d'expiration",
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WEBP, GIF).',
                    ]),
                ],
                'attr' => ['accept' => 'image/*'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
