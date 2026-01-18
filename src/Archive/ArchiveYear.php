<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

final class ArchiveYear
{
    private int $year;

    /** @var array<int, ArchiveMonth> */
    private array $months = [];

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function addMonth(int $month, int $count): void
    {
        if (isset($this->months[$month])) {
            $this->months[$month]->addCount($count);
            return;
        }

        $this->months[$month] = new ArchiveMonth($month, $count);
    }

    /**
     * @return array<int, int>
     */
    public function toArray(): array
    {
        if ($this->months === []) {
            return [];
        }

        $months = $this->months;
        ksort($months);

        $result = [];
        foreach ($months as $month => $archiveMonth) {
            $result[$month] = $archiveMonth->getCount();
        }

        return $result;
    }

    /**
     * @return array<int, ArchiveMonth>
     */
    public function getMonths(): array
    {
        return $this->months;
    }
}
