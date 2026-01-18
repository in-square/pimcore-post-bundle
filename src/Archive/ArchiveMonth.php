<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

final class ArchiveMonth
{
    private int $month;
    private int $count;

    public function __construct(int $month, int $count)
    {
        $this->month = $month;
        $this->count = $count;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function addCount(int $count): void
    {
        $this->count += $count;
    }
}
