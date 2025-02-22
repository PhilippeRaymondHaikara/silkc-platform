<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\CallbackTransformer;

class RecruiterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'login.form.label.company_name',
                'translation_domain' => 'messages',
                'required'   => true,
            ])
            ->add('homepage', TextType::class, [
                'label'              => 'login.form.label.homepage',
                'translation_domain' => 'messages',
                'required'           => false,
            ])
            ->add('address', HiddenType::class, [
                'attr'               => ["class" => "input_user_address"],
                'label'              => 'login.form.label.address',
                'translation_domain' => 'messages',
                'required'           => false,
            ])
            ->add('longitude', HiddenType::class, [
                'attr'               => ["class" => "user_lng"],
                'required'           => false,
            ])
            ->add('latitude', HiddenType::class, [
                'attr'               => ["class" => "user_lat"],
                'required'           => false,
            ]);

        if (array_key_exists('by_admin', $options) && $options['by_admin'] === true) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'label' => 'label.roles',
                    'multiple' => true,
                    'expanded' => true,
                    'required'   => true,
                    'choices' => User::getRolesList(),
                ]);
        }

        $currentYear = intval(date('Y'));
        for ($i = $currentYear; 1900 <= $i; $i--) {
            $dateChoices[$i] = $i;
        }   

        $builder
            ->add('email', EmailType::class, [
                'label' => 'login.form.label.email',
                'translation_domain' => 'messages',
            ])
            ->add('dateOfBirth', ChoiceType::class, [
                'choices' => $dateChoices,
                'label' => 'login.form.label.year_of_creation',
                'translation_domain' => 'messages',
                'required'   => false,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'login.form.label.year_of_creation'
            ]);

        if (array_key_exists('require_password', $options) && $options['require_password'] === true) {
            $builder->add('password', RepeatedType::class, [
                'type'               => PasswordType::class,
                'translation_domain' => 'messages',
                'required'           => (array_key_exists('require_password', $options) && $options['require_password'] === true) ? true : false,
                'first_options'      => ['label' => 'login.form.label.password.first', "always_empty" => true],
                'second_options'     => ['label' => 'login.form.label.password.second', 'always_empty' => true],
                'invalid_message'    => 'login.form.not_identical_password',
            ]);
        }
        if (array_key_exists('enable_password', $options) && $options['enable_password'] === true) {
            $builder->add('password', RepeatedType::class, [
                'type'               => PasswordType::class,
                'translation_domain' => 'messages',
                'required'           => false,
                'options'            => ['translation_domain' => 'messages'],
                'first_options'      => ['translation_domain' => 'messages', 'label' => 'login.form.label.password.first', "always_empty" => true],
                'second_options'     => ['translation_domain' => 'messages', 'label' => 'login.form.label.password.second', 'always_empty' => true],
                'invalid_message'    => 'login.form.not_identical_password',
            ]);
        }
        $builder->add('save', SubmitType::class, [
            'translation_domain' => 'messages',
            'label' => 'label.save'
        ]);

        $builder->get('dateOfBirth')->addModelTransformer(
            new CallbackTransformer(
                function ($datetimeToNumber) {
                    if ($datetimeToNumber === null)
                        return null;
                    else if ($datetimeToNumber instanceof \DateTime)
                        return intval($datetimeToNumber->format('Y'));

                    return $datetimeToNumber;
                },
                function ($numberToDatetime) {
                    if ($numberToDatetime === null)
                        return null;
                    else if (is_int($numberToDatetime))
                        return \DateTime::createFromFormat('Y', $numberToDatetime);

                    return $numberToDatetime;
                }
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'enable_password' => false,
                'require_password' => false,
                'is_personal' => false,
                'by_admin' => false,
                'csrf_protection' => false, // Possibilité de créer un compte en API
            ]
        );
    }
}