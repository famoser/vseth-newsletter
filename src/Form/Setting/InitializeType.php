<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Setting;

use App\Entity\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitializeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('periodStart', DateTimeType::class, ['widget' => 'single_text']);
        $builder->add('periodEnd', DateTimeType::class, ['widget' => 'single_text']);
        $builder->add('paymentPrefix', TextType::class, ['help' => 'help.payment_prefix']);

        $builder->add('reservations', FileType::class, ['mapped' => false, 'help' => 'help.reservations']);
        $builder->add('users', FileType::class, ['mapped' => false, 'help' => 'help.users']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'translation_domain' => 'entity_setting',
        ]);
    }
}
