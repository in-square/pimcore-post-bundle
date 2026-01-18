<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Pimcore\Model\DataObject\PostCategory;
use Pimcore\Model\DataObject\PostTag;

final class ArchiveBuilder implements ArchiveBuilderInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function buildForCategory(?PostCategory $category): ArchiveSummary
    {
        $summary = new ArchiveSummary();

        if (null === $category) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT year, month, count AS post_count
                FROM post_archive_by_category
                WHERE category_id IS NULL
                ORDER BY year DESC, month DESC'
            );

            return $this->hydrateSummary($summary, $rows);
        }

        $categoryId = $category->getId();
        if (null === $categoryId) {
            return $summary;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT year, month, count AS post_count
            FROM post_archive_by_category
            WHERE category_id = :categoryId
            ORDER BY year DESC, month DESC',
            ['categoryId' => $categoryId],
            ['categoryId' => ParameterType::INTEGER]
        );

        return $this->hydrateSummary($summary, $rows);
    }

    public function buildForTag(PostTag $tag): ArchiveSummary
    {
        $summary = new ArchiveSummary();

        $tagId = $tag->getId();
        if (null === $tagId) {
            return $summary;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT year, month, count AS post_count
            FROM post_archive_by_tag
            WHERE tag_id = :tagId
            ORDER BY year DESC, month DESC',
            ['tagId' => $tagId],
            ['tagId' => ParameterType::INTEGER]
        );

        return $this->hydrateSummary($summary, $rows);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function hydrateSummary(ArchiveSummary $summary, array $rows): ArchiveSummary
    {
        foreach ($rows as $row) {
            $summary->add(
                (int) $row['year'],
                (int) $row['month'],
                (int) $row['post_count']
            );
        }

        return $summary;
    }
}
