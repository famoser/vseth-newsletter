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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends BaseEntryType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('newsletter', EntityType::class, [
            'class' => Newsletter::class,
            'choice_label' => function (Newsletter $newsletter) {
                return $newsletter->getPlannedSendAt()->format('d.m.Y');
            },
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.sentAt IS NULL');
            },
            'translation_domain' => 'entity_newsletter',
            'label' => 'entity.name',
        ]);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
            'translation_domain' => 'entity_entry',
        ]);
    }
}
