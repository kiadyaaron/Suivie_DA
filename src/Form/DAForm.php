<?php

namespace App\Form;

use App\Entity\DA;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DAForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ReferenceDA', TextType::class, [
                'label' => 'Référence de la DA',
            ])
            ->add('DateCreationDA', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de création de la DA',
            ])
            ->add('EtatDA', ChoiceType::class, [
                'choices' => [
                    'Annulée' => 'Annulée',
                    'Validée' => 'Validée',
                    'Traîtée' => 'Traîtée',
                ],
                'label' => 'État de la DA',
            ])
            ->add('ChantierDepartement', TextType::class, [
                'label' => 'Chantier ou département'
            ])
            ->add('Description', TextType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('ReferenceBCA', TextType::class, [
                'label' => 'Référence BCA',
                'required' => false,
            ])
            ->add('CreationBCA', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de création BCA',
                'required' => false,
            ])
            ->add('DateLivraison', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de livraison',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DA::class,
        ]);
    }
}
