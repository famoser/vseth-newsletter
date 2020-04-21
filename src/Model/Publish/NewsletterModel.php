<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Publish;

use App\Entity\Newsletter;

class NewsletterModel
{
    /**
     * @var Newsletter
     */
    private $newsletter;

    /**
     * @var CategoryModel[]
     */
    private $categories;

    /**
     * NewsletterModel constructor.
     *
     * @param CategoryModel[] $categories
     */
    public function __construct(Newsletter $newsletter, array $categories)
    {
        $this->newsletter = $newsletter;
        $this->categories = $categories;
    }

    public function realId()
    {
        return $this->newsletter->getId();
    }

    public function getIntroduction(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->newsletter->getIntroductionDe();
        }

        return $this->newsletter->getIntroductionEn();
    }

    /**
     * @return CategoryModel[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
