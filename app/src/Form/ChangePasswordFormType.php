<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
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
        $builder
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
