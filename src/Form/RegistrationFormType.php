<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an ID'])
                ],
                'attr' => ['class' => 'entryForm', 'type' => 'text', 'placeholder' => 'ID', 'required', 'autofocus'],
                'error_bubbling' => true,
            ])
            ->add('email', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an email']),
                    new Email(['message' => 'Please enter a valid email']),
                ],
                'attr' => ['class' => 'entryForm', 'type' => 'email', 'placeholder' => 'Email', 'required', 'autofocus'],
                'error_bubbling' => true,
            ])
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'entryForm', 'type' => 'text', 'placeholder' => 'First Name', 'required', 'autofocus'],
                'error_bubbling' => true,
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'entryForm', 'type' => 'text', 'placeholder' => 'Last Name', 'required', 'autofocus'],
                'error_bubbling' => true,
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'error_bubbling' => true,
                //'attr' => ['class' => 'entryForm']
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', 'class' => 'entryForm', 'type' => 'password', 'placeholder' => 'Password', 'required', 'autofocus'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 64,
                        'maxMessage' => 'Your password should be at most {{ limit }} characters',
                    ]),
                ],
                'error_bubbling' => true,
            ])
        ;
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                // Extract id and email from form data
                $id = $data->getId() ?? '';
                $email = $data->getEmail() ?? '';

                // Custom validation logic
                if (str_starts_with($id, 'r') && !str_ends_with($email, '@student.kuleuven.be')) {
                    $form->get('email')->addError(new FormError('If ID starts with "r", email must end with "@student.kuleuven.be"'));
                } elseif (str_starts_with($id, 'u') && !str_ends_with($email, '@kuleuven.be')) {
                    $form->get('email')->addError(new FormError('If ID starts with "u", email must end with "@kuleuven.be"'));
                } elseif (!str_starts_with($id, 'r') && !str_starts_with($id, 'u')) {
                    $form->get('id')->addError(new FormError('ID must start with "r" or "u"'));
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
