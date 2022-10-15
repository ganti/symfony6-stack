<?php

namespace App\Form\Core;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    private function t($message, $params=[])
    {
        return $this->translator->trans($message, $params, 'core');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        if($options['ask_username'])
        {
            $builder ->add('username', TextType::class, [
                'label' => $this->t('service.registration.form.username.label'),
                'attr' => [ 'placeholder' => $this->t('service.registration.form.username.placeholder'),
                            'autocomplete' => 'username',
                            'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->t('service.registration.form.email.messages.blank'),
                    ]),
                ],
            ]);
        }

        if($options['ask_name'])
        {
            $builder ->add('firstname', TextType::class, [
                'label' => $this->t('service.registration.form.firstname.label'),
                'attr' => [ 'placeholder' => $this->t('service.registration.form.firstname.placeholder'),
                            'autocomplete' => 'firstname',
                            'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->t('service.registration.form.firstname.messages.blank'),
                    ]),
                ],
            ]);
            $builder ->add('lastname', TextType::class, [
                'label' => $this->t('service.registration.form.lastname.label'),
                'attr' => [ 'placeholder' => $this->t('service.registration.form.lastname.placeholder'),
                            'autocomplete' => 'lastname',
                            'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->t('service.registration.form.lastname.messages.blank'),
                    ]),
                ],
            ]);
        }

        $builder
            ->add('email', EmailType::class, [
                'label' => $this->t('service.registration.form.email.label'),
                'attr' => [ 'placeholder' => $this->t('service.registration.form.email.placeholder'),
                            'autocomplete' => 'email',
                            'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->t('service.registration.form.email.messages.blank'),
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => $this->t('service.registration.form.password.messages.no_match'),
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => [   'label' => $this->t('service.registration.form.password.label'),
                                        'attr' => [ 'class' => 'form-control'],
                                        'constraints' => [
                                            new NotBlank([
                                                'message' => $this->t('service.registration.form.password.messages.blank'),
                                            ]),
                                            new Length([
                                                'min' => 6,
                                                'minMessage' => $this->t('service.registration.form.password.messages.min_lenght', ['limit' => 6]),
                                                'max' => 4096,
                                            ]),
                                        ],
                                    ],
                'second_options' => [   'label' => $this->t('service.registration.form.password.repeat_label'),
                                        'attr' => [ 'class' => 'form-control'],
                                    ],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],

            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => $this->t('service.registration.form.terms.label'),
                'attr' => [ 'required' => 'true',
                            'class' => 'form-check-input',
                            'style' => 'width: 3em;',
                            'role' => 'switch',
                            'for' => 'term'
                ],
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => $this->t('service.registration.form.terms.messages.agree'),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'ask_username' => false,
            'ask_name' => false,
            'fistname' => null,
            'lastname' => null,
            
        ]);
    }
}
