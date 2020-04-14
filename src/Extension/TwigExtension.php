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

use App\Entity\Entry;
use App\Enum\BooleanType;
use App\Enum\OrganisationCategoryType;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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
            new TwigFilter('camelCaseToUnderscore', [$this, 'camelCaseToUnderscoreFilter']),
            new TwigFilter('leftTrimLines', [$this, 'leftTrimLines']),
            new TwigFilter('entryEventInfo', [$this, 'entryEventInfo']),
        ];
    }

    public function leftTrimLines(string $input): string
    {
        $lines = explode("\n", $input);

        $resultLines = [];
        foreach ($lines as $line) {
            $resultLines[] = ltrim($line);
        }

        return implode("\n", $resultLines);
    }

    public function entryEventInfo(Entry $entry, string $locale): string
    {
        $dateFormat = $this->translator->trans('time.format.date', [], 'framework', $locale);

        $result = '';
        if ($entry->getStartDate() !== null) {
            $startDate = $entry->getStartDate()->format($dateFormat);
            $result .= $startDate;
            if ($entry->getStartTime() !== null) {
                $result .= ' ' . mb_substr($entry->getStartTime(), 0, 5);
            }
            if ($entry->getEndDate() !== null) {
                $endDate = $entry->getEndDate()->format($dateFormat);
                $showDate = $startDate !== $endDate;
                $showTime = $entry->getEndTime() !== null;

                if ($showDate || $showTime) {
                    $result .= ' -';

                    if ($showDate) {
                        $result .= ' ' . $endDate;
                    }

                    if ($showTime) {
                        $result .= ' ' . mb_substr($entry->getEndTime(), 0, 5);
                    }
                }
            }
        }

        if ($entry->getLocation() !== null) {
            if (mb_strlen($result) > 0) {
                $result .= ', ';
            }
            $result .= $entry->getLocation();
        }

        return $result;
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
     * @param string|null $locale
     *
     * @return string
     */
    public function dateTimeFilter($date, $locale = null)
    {
        if ($date instanceof \DateTime) {
            $dateTimeFormat = $this->translator->trans('time.format.date_time', [], 'framework', $locale);

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
