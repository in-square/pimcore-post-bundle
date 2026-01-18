<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

final class ArchiveSummary
{
    /** @var array<int, ArchiveYear> */
    private array $years = [];

    public function add(int $year, int $month, int $count): void
    {
        if (!isset($this->years[$year])) {
            $this->years[$year] = new ArchiveYear($year);
        }

        $this->years[$year]->addMonth($month, $count);
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function toArray(): array
    {
        if ($this->years === []) {
            return [];
        }

        $years = $this->years;
        krsort($years);

        $result = [];
        foreach ($years as $year => $archiveYear) {
            $result[$year] = $archiveYear->toArray();
        }

        return $result;
    }

    /**
     * @return array<int, ArchiveYear>
     */
    public function getYears(): array
    {
        return $this->years;
    }
}
