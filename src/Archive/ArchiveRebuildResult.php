<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

final class ArchiveRebuildResult
{
    public function __construct(
        private int $processedPosts,
        private int $categoryRows,
        private int $tagRows,
        private int $globalBuckets,
        private bool $success
    ) {
    }

    public function getProcessedPosts(): int
    {
        return $this->processedPosts;
    }

    public function getCategoryRows(): int
    {
        return $this->categoryRows;
    }

    public function getTagRows(): int
    {
        return $this->tagRows;
    }

    public function getGlobalBuckets(): int
    {
        return $this->globalBuckets;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
