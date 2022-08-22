<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use function App\Controller\Admin\t;

class RegistrationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => t('admin.service.registration.form.email.label'),
                'attr' => [ 'placeholder' => t('admin.service.registration.form.email.placeholder'),
                            'autocomplete' => 'email',
                            'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => [   'label' => t('admin.service.registration.form.password.label'),
                                        'attr' => [ 'class' => 'form-control'],
                                        'constraints' => [
                                            new NotBlank([
                                                'message' => 'Please enter a password',
                                            ]),
                                            new Length([
                                                'min' => 6,
                                                'minMessage' => 'Your password should be at least {{ limit }} characters',
                                                'max' => 4096,
                                            ]),
                                        ],
                                    ],
                'second_options' => [   'label' => t('admin.service.registration.form.password.repeat_label'),
                                        'attr' => [ 'class' => 'form-control'],
                                    ],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => t('admin.service.registration.form.terms.label'),
                'attr' => [ 'required' => 'false',
                            'class' => 'form-check-input',
                            'style' => 'width: 3em;',
                            'role' => 'switch',
                            'for' => 'term'
                ],
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => t('admin.service.registration.form.terms.messages.agree'),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
