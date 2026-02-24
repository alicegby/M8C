<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('avatarFile', FileType::class, [
                'label' => 'Avatar (jpg, png, gif)',
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
            ->add('removeAvatar', CheckboxType::class, [
                'label'    => 'Supprimer l’avatar actuel ? (cochez si vous souhaitez le supprimer)',
                'mapped'   => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}