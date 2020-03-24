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
use App\Entity\Newsletter;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('newsletter', EntityType::class, [
            'class' => Newsletter::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.sentAt IS NULL');
            },
        ]);
        $builder->add('organizer', TextType::class);
        $builder->add('titleDe', TextType::class);
        $builder->add('titleEn', TextType::class);
        $builder->add('descriptionDe', TextType::class);
        $builder->add('descriptionEn', TextType::class);
        $builder->add('linkDe', TextType::class, ['required' => false]);
        $builder->add('linkEn', TextType::class, ['required' => false]);
        $builder->add('startAt', TextType::class, ['required' => false]);
        $builder->add('endAt', TextType::class, ['required' => false]);
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
