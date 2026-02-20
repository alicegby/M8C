<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\Clue;
use App\Entity\MurderParty;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $murderParty = $options['murder_party'];

        $builder
            ->add('content', TextareaType::class, ['label' => 'Contenu de l\'indice', 'attr' => ['rows' => 4]])
            ->add('triggerMinutes', IntegerType::class, ['label' => 'Déclenchement (minutes)'])
            ->add('isPublic', CheckboxType::class, ['label' => 'Indice public', 'required' => false])
            ->add('character', EntityType::class, [
                'label' => 'Personnage destinataire (si individuel)',
                'class' => Character::class,
                'choices' => $murderParty ? $murderParty->getCharacters() : [],
                'choice_label' => fn(Character $c) => $c->getPrenom() . ' ' . $c->getNom(),
                'required' => false,
                'placeholder' => 'Public (tous les joueurs)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clue::class,
            'murder_party' => null,
        ]);
    }
}