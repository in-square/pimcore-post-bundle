<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

use Psr\Cache\CacheItemPoolInterface;

final class ArchiveChangeTracker
{
    private const CACHE_KEY = 'in_square_post.last_posts_change';

    public function __construct(private CacheItemPoolInterface $cachePool)
    {
    }

    public function markChanged(): void
    {
        $item = $this->cachePool->getItem(self::CACHE_KEY);
        $item->set(time());
        $this->cachePool->save($item);
    }

    public function getLastChangeTimestamp(): ?int
    {
        $item = $this->cachePool->getItem(self::CACHE_KEY);

        if (!$item->isHit()) {
            return null;
        }

        $value = $item->get();

        return is_int($value) ? $value : (int) $value;
    }

    public function clear(): void
    {
        $this->cachePool->deleteItem(self::CACHE_KEY);
    }
}
