<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

use Pimcore\Model\DataObject\PostCategory;
use Pimcore\Model\DataObject\PostTag;

interface ArchiveBuilderInterface
{
    public function buildForCategory(?PostCategory $category): ArchiveSummary;

    public function buildForTag(PostTag $tag): ArchiveSummary;
}
