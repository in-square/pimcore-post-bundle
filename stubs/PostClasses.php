<?php

declare(strict_types=1);

namespace Pimcore\Model\DataObject;

use DateTimeInterface;

class Post extends Concrete
{
    public function getDate(): ?DateTimeInterface
    {
    }

    /**
     * @return array<int, PostCategory>|null
     */
    public function getCategories(): ?array
    {
    }

    /**
     * @return array<int, PostTag>|null
     */
    public function getTags(): ?array
    {
    }
}

class PostCategory extends Concrete
{
}

class PostTag extends Concrete
{
}

namespace Pimcore\Model\DataObject\Post;

class Listing extends \Pimcore\Model\DataObject\Listing\Concrete
{
}
