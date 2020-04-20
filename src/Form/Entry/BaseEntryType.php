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

use App\Entity\Category;
use App\Entity\Entry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Entry $entry */
            $entry = $event->getData();

            $event->getForm()->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => function (Category $category) {
                    return $category->getNameEn();
                },
                'choices' => $entry->getNewsletter()->getCategories(),
                'required' => false,
                'translation_domain' => 'entity_category',
                'label' => 'entity.name',
            ]);
        });

        $builder->add('titleDe', TextType::class);
        $builder->add('titleEn', TextType::class);
        $builder->add('descriptionDe', TextareaType::class, ['attr' => ['maxlength' => '300']]);
        $builder->add('descriptionEn', TextareaType::class, ['attr' => ['maxlength' => '300']]);
        $builder->add('linkDe', TextType::class, ['required' => false]);
        $builder->add('linkEn', TextType::class, ['required' => false]);
        $builder->add('startDate', DateType::class, ['widget' => 'single_text', 'required' => false]);
        $builder->add('startTime', TimeType::class, ['widget' => 'single_text', 'input' => 'string', 'required' => false]);
        $builder->add('endDate', DateType::class, ['widget' => 'single_text', 'required' => false]);
        $builder->add('endTime', TimeType::class, ['widget' => 'single_text', 'input' => 'string', 'required' => false]);
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
