<?php

namespace App\Form;

use App\Entity\MurderParty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{TextType, TextareaType, IntegerType, MoneyType, CheckboxType, FileType};
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{NotBlank, Positive, File};
use App\Form\CharacterType;
use App\Form\ClueType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MurderPartyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre', 'constraints' => [new NotBlank()]])
            ->add('slug', TextType::class, ['label' => 'Slug (URL)', 'constraints' => [new NotBlank()]])
            ->add('synopsis', TextareaType::class, ['label' => 'Synopsis', 'attr' => ['rows'=>10]])
            ->add('scenario', TextareaType::class, ['label' => 'Scénario complet', 'attr' => ['rows'=>10]])
            ->add('epilogue', TextareaType::class, ['label' => 'Épilogue', 'attr' => ['rows'=>10]])
            ->add('duree', IntegerType::class, ['label' => 'Durée (minutes)', 'constraints' => [new Positive()]])
            ->add('nbPlayers', IntegerType::class, ['label' => 'Nombre de joueurs', 'constraints' => [new Positive()]])
            ->add('price', MoneyType::class, ['label' => 'Prix', 'currency'=>'EUR'])
            ->add('isFree', CheckboxType::class, ['label'=>'Gratuite','required'=>false])
            ->add('isPublished', CheckboxType::class, ['label'=>'Publiée','required'=>false])
            ->add('coverImageUrl', FileType::class, [
                'label' => 'Cover Murder Party (jpg, png, gif)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'=>'2M',
                        'mimeTypes'=>['image/jpeg','image/png','image/gif'],
                        'mimeTypesMessage'=>'Merci de télécharger une image valide (jpg, png, gif)',
                    ])
                ]
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
                'entry_options' => [
                    'murder_party' => null, // sera écrasé par l'event
                ],
            ]);

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $mp = $event->getData();
                $form = $event->getForm();

                if ($mp && $mp->getId()) {
                    $form->add('clues', CollectionType::class, [
                        'entry_type' => ClueType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'label' => false,
                        'entry_options' => [
                            'murder_party' => $mp,
                        ],
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MurderParty::class]);
    }
}