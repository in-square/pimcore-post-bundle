<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Tests\Service;

use InSquare\PimcorePostBundle\Service\PostFolderOrganizer;
use InSquare\PimcorePostBundle\Service\PostSettings;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject\Post;
use Pimcore\Model\Element\DuplicateFullPathException;

final class PostFolderOrganizerTest extends TestCase
{
    public function testMoveToDateFolderRetriesWithUniqueKeyOnDuplicatePath(): void
    {
        $post = new TestPost(true);
        $organizer = new TestPostFolderOrganizer(new PostSettings([]));

        $result = $organizer->moveToDateFolder($post, new \DateTimeImmutable('2025-01-01'));

        self::assertTrue($result);
        self::assertSame(2, $post->saveCalls);
        self::assertSame('unique-key', $post->key);
    }

    public function testMoveToDateFolderSavesOnceWhenNoDuplicate(): void
    {
        $post = new TestPost(false);
        $organizer = new TestPostFolderOrganizer(new PostSettings([]));

        $result = $organizer->moveToDateFolder($post, new \DateTimeImmutable('2025-01-01'));

        self::assertTrue($result);
        self::assertSame(1, $post->saveCalls);
        self::assertNull($post->key);
    }
}

final class TestPostFolderOrganizer extends PostFolderOrganizer
{
    public function assignParentForDate(Post $post, ?\DateTimeInterface $date = null): bool
    {
        return true;
    }

    protected function generateUniqueKey(Post $post): string
    {
        return 'unique-key';
    }
}

final class TestPost extends Post
{
    public int $saveCalls = 0;
    public ?string $key = null;

    public function __construct(private bool $throwDuplicate)
    {
    }

    public function save(): void
    {
        $this->saveCalls++;

        if ($this->throwDuplicate && $this->saveCalls === 1) {
            throw new DuplicateFullPathException('Duplicate full path');
        }
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }
}
