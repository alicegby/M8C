<?php 

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('prenom', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('email', EmailType::class, ['constraints' => [new Assert\NotBlank(), new Assert\Email()]])
            ->add('sujet', ChoiceType::class, [
                'choices' => [
                    'Demande d\'informations' => 'Demande d\'informations',
                    'Demande de Murder Party personnalisée' => 'Demande de Murder Party personnalisée',
                    'Autre' => 'Autre'
                ],
                'placeholder' => 'Sélectionnez un sujet',
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('sousMenu', ChoiceType::class, [
                'choices' => [
                    'Anniversaire' => 'Anniversaire',
                    'Team building / Entreprise' => 'Team building / Entreprise',
                    'Soirée privée' => 'Soirée privée',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Type d\'événement',
                'required' => false,
                'label' => 'Type d\'événement'
            ])
            ->add('nombrePersonnes', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Ex : 10 personnes, entre 8 et 12...']
            ])
            ->add('dateEvenement', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Ex : 15 mars, entre le 01/02 et le 03/02...']
            ])
            ->add('message', TextareaType::class, ['constraints' => [new Assert\NotBlank()]]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}