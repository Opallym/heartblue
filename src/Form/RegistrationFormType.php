<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email as EmailAssert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required'   => false,
                'label'      => 'Nom complet',
                'empty_data' => '',
            ])
            ->add('age', IntegerType::class, [
                'required' => false,
                'label'    => 'Âge',
            ])
            ->add('city', TextType::class, [
                'required'   => false,
                'label'      => 'Ville',
                'empty_data' => '',
            ])
            ->add('country', TextType::class, [
                'required'   => false,
                'label'      => 'Pays',
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Email',
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer une adresse email'),
                    new EmailAssert(message: "L'adresse email n'est pas valide"),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped'      => false,
                'attr'        => ['autocomplete' => 'new-password'],
                'label'       => 'Mot de passe',
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer un mot de passe'),
                    new Length(min: 6, minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères', max: 60),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped'      => false,
                'label'       => "J’accepte les Conditions Générales d'Utilisation",
                'constraints' => [
                    new IsTrue(message: 'Vous devez accepter les conditions avant de continuer'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => User::class,
            'csrf_protection'    => true,
            'csrf_field_name'    => '_token',
            'csrf_token_id'      => 'registration_form',
            'translation_domain' => false,
        ]);
    }
}
