<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Service;

use Pimcore\Model\DataObject\Post;
use Pimcore\Model\DataObject\PostCategory;
use Pimcore\Model\DataObject\Service as DataObjectService;
use Pimcore\Model\Element\Service as ElementService;
use Pimcore\Model\Element\DuplicateFullPathException;

final class PostFolderOrganizer
{
    public function __construct(private PostSettings $settings)
    {
    }

    public function moveToDateFolder(Post $post, ?\DateTimeInterface $date = null): bool
    {
        if (!$this->assignParentForDate($post, $date)) {
            return false;
        }

        try {
            $post->save();
        } catch (DuplicateFullPathException) {
            $post->setKey($this->generateUniqueKey($post));
            $post->save();
        }

        return true;
    }

    public function assignParentForDate(Post $post, ?\DateTimeInterface $date = null): bool
    {
        $date = $date ?? $this->resolveDate($post);
        if (!$date instanceof \DateTimeInterface) {
            return false;
        }

        $folderPath = $this->buildTargetPath($post, $date);
        $folder = DataObjectService::createFolderByPath($folderPath);

        if (null === $folder) {
            return false;
        }

        if ($post->getParentId() === $folder->getId()) {
            return false;
        }

        $post->setParent($folder);

        return true;
    }

    public function resolveDate(Post $post): ?\DateTimeInterface
    {
        $field = $this->settings->getSortingDateField();
        $getter = 'get' . ucfirst($field);

        if (!method_exists($post, $getter)) {
            return null;
        }

        $value = $post->$getter();

        return $value instanceof \DateTimeInterface ? $value : null;
    }

    private function buildTargetPath(Post $post, \DateTimeInterface $date): string
    {
        $root = trim($this->settings->getPostRootFolder());
        $root = '/' . trim($root, '/');

        $categorySegment = $this->resolveCategorySegment($post);
        $datePath = $date->format('Y/m/d');

        $path = trim($root, '/');
        if ($categorySegment !== null) {
            $path = trim($path . '/' . $categorySegment, '/');
        }
        $path = trim($path . '/' . $datePath, '/');

        return '/' . $path;
    }

    private function resolveCategorySegment(Post $post): ?string
    {
        if (!method_exists($post, 'getCategory')) {
            return null;
        }

        $category = $post->getCategory();
        if (!$category instanceof PostCategory) {
            return null;
        }

        $candidate = (string) $category->getKey();

        $valid = ElementService::getValidKey($candidate, 'object');

        return $valid !== '' ? $valid : null;
    }

    protected function generateUniqueKey(Post $post): string
    {
        return DataObjectService::getUniqueKey($post);
    }
}
