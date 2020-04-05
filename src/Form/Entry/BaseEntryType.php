<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Entry;

use App\Entity\Entry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titleDe', TextType::class);
        $builder->add('titleEn', TextType::class);
        $builder->add('descriptionDe', TextareaType::class);
        $builder->add('descriptionEn', TextareaType::class);
        $builder->add('linkDe', TextType::class, ['required' => false]);
        $builder->add('linkEn', TextType::class, ['required' => false]);
        $builder->add('startAt', DateTimeType::class, ['date_widget' => 'single_text', 'time_widget' => 'single_text', 'required' => false]);
        $builder->add('endAt', DateTimeType::class, ['date_widget' => 'single_text', 'time_widget' => 'single_text', 'required' => false]);
        $builder->add('location', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
            'translation_domain' => 'entity_entry',
        ]);
    }
}
