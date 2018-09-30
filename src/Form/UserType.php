<?php
// src/Form/UserType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'attr'=>array(
                    'placeholder' => 'Your email address',
                    'class' => 'form-control',
                )
            ))
            ->add('username', TextType::class, array(
                'attr'=>array(
                    'placeholder' => 'Your username',
                    'class' => 'form-control',
                )
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array(
                    'label' => 'Password',
                    'attr' => array(
                        'placeholder' => 'Your Password',
                        'class' => 'form-control',
                    ),
                ),
                'second_options' => array(
                    'label' => 'Repeat Password',
                    'attr' => array(
                        'placeholder' => 'Confirm Password',
                        'class' => 'form-control',
                    ),
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}
