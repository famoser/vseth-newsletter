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
use App\Entity\Organisation;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEntryType extends BaseEntryType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('organisation', EntityType::class, [
            'class' => Organisation::class,
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.hiddenAt IS NULL')
                    ->orderBy('u.name', 'ASC');
            },
        ]);
        $builder->add('priority', NumberType::class);

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
