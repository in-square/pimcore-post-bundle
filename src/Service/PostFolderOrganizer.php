<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Service;

use Pimcore\Model\DataObject\Post;
use Pimcore\Model\DataObject\Service as DataObjectService;
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

        $folderPath = $this->buildTargetPath($date);
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

    private function buildTargetPath(\DateTimeInterface $date): string
    {
        $root = trim($this->settings->getPostRootFolder());
        $root = '/' . trim($root, '/');

        $datePath = $date->format('Y/m/d');

        if ($root === '/') {
            return '/' . $datePath;
        }

        return $root . '/' . $datePath;
    }

    protected function generateUniqueKey(Post $post): string
    {
        return DataObjectService::getUniqueKey($post);
    }
}
