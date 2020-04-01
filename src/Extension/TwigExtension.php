<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extension;

use App\Enum\BooleanType;
use App\Enum\OrganisationCategoryType;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private $translator;
    private $entrypointLookup;
    private $publicDir;

    public function __construct(TranslatorInterface $translator, ParameterBagInterface $parameterBag, EntrypointLookupInterface $entrypointLookup)
    {
        $this->translator = $translator;
        $this->entrypointLookup = $entrypointLookup;
        $this->publicDir = $parameterBag->get('PUBLIC_DIR');
    }

    /**
     * makes the filters available to twig.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('dateFormat', [$this, 'dateFormatFilter']),
            new TwigFilter('timeFormat', [$this, 'timeFormatFilter']),
            new TwigFilter('dateTimeFormat', [$this, 'dateTimeFilter']),
            new TwigFilter('booleanFormat', [$this, 'booleanFilter']),
            new TwigFilter('organisationCategoryText', [$this, 'organisationCategoryTextFilter']),
            new TwigFilter('camelCaseToUnderscore', [$this, 'camelCaseToUnderscoreFilter']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
            new TwigFunction('load_public_file', [$this, 'loadPublicFile']),
        ];
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $files = $this->entrypointLookup
            ->getCssFiles($entryName);

        $source = '';
        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDir . '/' . $file);
        }

        return $source;
    }

    public function loadPublicFile(string $entryName): string
    {
        $path = $this->publicDir . '/' . $entryName;

        return file_get_contents($path);
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function camelCaseToUnderscoreFilter($propertyName)
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $propertyName));
    }

    /**
     * @param $date
     *
     * @return string
     */
    public function dateFormatFilter($date, $prependDay = true)
    {
        if ($date instanceof \DateTime) {
            $dateFormat = $this->translator->trans('time.format.date', [], 'framework');

            $prepend = $prependDay ? $this->prependDayName($date) . ', ' : '';

            return $prepend . $date->format($dateFormat);
        }

        return '-';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function organisationCategoryTextFilter($value)
    {
        if (\is_int($value)) {
            return OrganisationCategoryType::getTranslation($value, $this->translator);
        }

        return '-';
    }

    /**
     * @param $date
     *
     * @return string
     */
    public function dateTimeFilter($date)
    {
        if ($date instanceof \DateTime) {
            $dateTimeFormat = $this->translator->trans('time.format.date_time', [], 'framework');

            return $this->prependDayName($date) . ', ' . $date->format($dateTimeFormat);
        }

        return '-';
    }

    /**
     * @param $date
     *
     * @return string
     */
    public function timeFormatFilter($date)
    {
        if (\is_string($date)) {
            return mb_substr($date, 0, 5);
        }

        return '-';
    }

    /**
     * translates the day of the week.
     *
     * @return string
     */
    private function prependDayName(DateTime $date)
    {
        return $this->translator->trans('time.weekdays.' . $date->format('D'), [], 'framework');
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function booleanFilter($value)
    {
        if ($value) {
            return BooleanType::getTranslation(BooleanType::YES, $this->translator);
        }

        return BooleanType::getTranslation(BooleanType::NO, $this->translator);
    }
}
