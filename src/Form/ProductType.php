<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Type;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => [
                    'placeholder' => "Entrer le nom du produit"
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du produit'
                ]
            ])
            ->add('img', FileType::class, [
                'label' => 'Image',
                // Ici required permet au user  
                'required' => false,
                'mapped' => false
            ]) 
            ->add('brand', TextType::class, [
                'label' => 'Marque',
                'attr' => [
                    'placeholder' => 'Marque du produit'
                ]
            ])
            ->add('type', EntityType::class, [
                'class' => type::class,
                'choice_label' => 'name',
            ])
            // ->add('owner', EntityType::class, [
            //     'class' => user::class,
            //     'choice_label' => 'email',
            // ])
            // ->add('borrower', EntityType::class, [
            //     'class' => user::class,
            //     'choice_label' => 'email',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
