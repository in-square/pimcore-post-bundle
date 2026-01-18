<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Archive;

use Doctrine\DBAL\Connection;
use Pimcore\Model\DataObject\Post;
use Pimcore\Model\DataObject\Post\Listing as PostListing;
use Pimcore\Model\DataObject\PostCategory;
use Pimcore\Model\DataObject\PostTag;

final class ArchiveRebuilder
{
    public function __construct(private Connection $connection)
    {
    }

    public function rebuild(): ArchiveRebuildResult
    {
        $categoryCounts = [];
        $tagCounts = [];
        $globalCounts = [];
        $processed = 0;

        if (!class_exists(Post::class) || !class_exists(PostListing::class)) {
            return new ArchiveRebuildResult(0, 0, 0, 0, false);
        }

        $listing = new PostListing();
        $listing->setUnpublished(false);

        foreach ($listing as $post) {
            if (!$post instanceof Post) {
                continue;
            }

            $date = $post->getDate();
            if (!$date instanceof \DateTimeInterface) {
                continue;
            }

            $year = (int) $date->format('Y');
            $month = (int) $date->format('n');

            $globalCounts[$year][$month] = ($globalCounts[$year][$month] ?? 0) + 1;

            $categories = $post->getCategories();
            if (is_array($categories)) {
                foreach ($categories as $category) {
                    if (!$category instanceof PostCategory) {
                        continue;
                    }

                    $categoryId = $category->getId();
                    if (null === $categoryId) {
                        continue;
                    }

                    $categoryCounts[$categoryId][$year][$month] = ($categoryCounts[$categoryId][$year][$month] ?? 0) + 1;
                }
            }

            $tags = $post->getTags();
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    if (!$tag instanceof PostTag) {
                        continue;
                    }

                    $tagId = $tag->getId();
                    if (null === $tagId) {
                        continue;
                    }

                    $tagCounts[$tagId][$year][$month] = ($tagCounts[$tagId][$year][$month] ?? 0) + 1;
                }
            }

            $processed++;
        }

        $insertedCategory = 0;
        $insertedTag = 0;
        $globalRows = 0;

        $this->connection->beginTransaction();

        try {
            $this->connection->executeStatement('TRUNCATE TABLE post_archive_by_category');
            $this->connection->executeStatement('TRUNCATE TABLE post_archive_by_tag');

            foreach ($globalCounts as $year => $months) {
                foreach ($months as $month => $count) {
                    $this->connection->executeStatement(
                        'INSERT INTO post_archive_by_category (category_id, year, month, count)
                        VALUES (:categoryId, :year, :month, :count)',
                        [
                            'categoryId' => null,
                            'year' => $year,
                            'month' => $month,
                            'count' => $count,
                        ]
                    );
                    $insertedCategory++;
                    $globalRows++;
                }
            }

            foreach ($categoryCounts as $categoryId => $years) {
                foreach ($years as $year => $months) {
                    foreach ($months as $month => $count) {
                        $this->connection->executeStatement(
                            'INSERT INTO post_archive_by_category (category_id, year, month, count)
                            VALUES (:categoryId, :year, :month, :count)',
                            [
                                'categoryId' => $categoryId,
                                'year' => $year,
                                'month' => $month,
                                'count' => $count,
                            ]
                        );
                        $insertedCategory++;
                    }
                }
            }

            foreach ($tagCounts as $tagId => $years) {
                foreach ($years as $year => $months) {
                    foreach ($months as $month => $count) {
                        $this->connection->executeStatement(
                            'INSERT INTO post_archive_by_tag (tag_id, year, month, count)
                            VALUES (:tagId, :year, :month, :count)',
                            [
                                'tagId' => $tagId,
                                'year' => $year,
                                'month' => $month,
                                'count' => $count,
                            ]
                        );
                        $insertedTag++;
                    }
                }
            }

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }

        return new ArchiveRebuildResult($processed, $insertedCategory, $insertedTag, $globalRows, true);
    }
}
