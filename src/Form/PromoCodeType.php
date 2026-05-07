<?php

namespace App\Form;

use App\Entity\PromoCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, ['label' => 'Code promo'])
            ->add('discountType', ChoiceType::class, [
                'label' => 'Type de remise',
                'choices' => ['Pourcentage (%)' => 'percentage', 'Montant fixe (€)' => 'fixed'],
            ])
            ->add('discountValue', MoneyType::class, ['label' => 'Valeur de la remise', 'currency' => ''])
            ->add('validFrom', DateTimeType::class, ['label' => 'Valide à partir du', 'required' => false, 'widget' => 'single_text'])
            ->add('validUntil', DateTimeType::class, ['label' => 'Valide jusqu\'au', 'required' => false, 'widget' => 'single_text'])
            ->add('maxUses', IntegerType::class, ['label' => 'Nombre max d\'utilisations', 'required' => false])
            ->add('isActive', CheckboxType::class, ['label' => 'Actif', 'required' => false])
            ->add('isWelcomeCode', CheckboxType::class, ['label' => 'Code de bienvenue', 'required' => false])
            ->add('applicableTo', ChoiceType::class, [
                'label' => 'Applicable sur',
                'choices' => [
                    'Murder Parties uniquement' => 'mp',
                    'Packs uniquement' => 'pack',
                    'Les deux' => 'both',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PromoCode::class]);
    }
}