<?php

namespace App\Form;

use App\Entity\MurderParty;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\CharacterType;
use App\Form\ClueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File; 
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class MurderPartyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre', 'constraints' => [new NotBlank()]])
            ->add('slug', TextType::class, ['label' => 'Slug (URL)', 'constraints' => [new NotBlank()]])
            ->add('synopsis', TextareaType::class, ['label' => 'Synopsis', 'attr' => ['rows' => 10]])
            ->add('scenario', TextareaType::class, ['label' => 'Scénario complet', 'attr' => ['rows' => 10]])
            ->add('epilogue', TextareaType::class, ['label' => 'Épilogue', 'attr' => ['rows' => 10]])
            ->add('duree', IntegerType::class, ['label' => 'Durée (minutes)', 'constraints' => [new Positive()]])
            ->add('nbPlayers', IntegerType::class, ['label' => 'Nombre de joueurs', 'constraints' => [new Positive()]])
            ->add('price', MoneyType::class, ['label' => 'Prix', 'currency' => 'EUR'])
            ->add('isFree', CheckboxType::class, ['label' => 'Gratuite', 'required' => false])
            ->add('isPublished', CheckboxType::class, ['label' => 'Publiée', 'required' => false])
            ->add('coverImageUrl', FileType::class, [
                'label' => 'Cover Murder Party (jpg, png, gif)',
                'mapped' => false, // pas lié directement à l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Merci de télécharger une image valide (jpg, png, gif)',
                    ])
                ],
            ])
            ->add('characters', CollectionType::class, [
                'entry_type' => CharacterType::class,
                'allow_add' => true,   
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
            ->add('clues', CollectionType::class, [
                'entry_type' => ClueType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MurderParty::class]);
    }
}

