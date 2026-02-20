<?php

namespace App\Form;

use App\Entity\MurderParty;
use App\Entity\Pack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du pack'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('price', MoneyType::class, ['label' => 'Prix', 'currency' => 'EUR'])
            ->add('isActive', CheckboxType::class, ['label' => 'Actif', 'required' => false])
            ->add('murderParties', EntityType::class, [
                'label' => 'Murder Parties incluses',
                'class' => MurderParty::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Pack::class]);
    }
}