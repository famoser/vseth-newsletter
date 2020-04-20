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

use App\Entity\Category;

class CategoryModel
{
    /**
     * @var Category
     */
    private $category;

    /**
     * @var EntryModel[]
     */
    private $entries;

    /**
     * CategoryModel constructor.
     *
     * @param EntryModel[] $entries
     */
    public function __construct(Category $category, array $entries)
    {
        $this->category = $category;
        $this->entries = $entries;
    }

    public function getName(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->category->getNameDe();
        }

        return $this->category->getNameEn();
    }

    /**
     * @return EntryModel[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
