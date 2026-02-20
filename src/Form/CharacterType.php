<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom', 'required' => false])
            ->add('age', IntegerType::class, ['label' => 'Âge', 'required' => false])
            ->add('job', TextType::class, ['label' => 'Métier', 'required' => false])
            ->add('histoire', TextareaType::class, ['label' => 'Histoire', 'attr' => ['rows' => 50]])
            ->add('mobile', TextareaType::class, ['label' => 'Mobile', 'attr' => ['rows' => 20]])
            ->add('alibi', TextareaType::class, ['label' => 'Alibi / Lors du Crime', 'attr' => ['rows' => 50]])
            ->add('extraInfo', TextareaType::class, ['label' => 'Infos supplémentaires', 'required' => false, 'attr' => ['rows' => 20]])
            ->add('isGuilty', CheckboxType::class, ['label' => 'Coupable', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Character::class]);
    }
}