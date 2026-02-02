<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\EventSubscriber;

use InSquare\PimcorePostBundle\Service\PostFolderOrganizer;
use InSquare\PimcorePostBundle\Service\PostSettings;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Post;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PostFolderSortSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PostFolderOrganizer $organizer,
        private PostSettings $settings
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_ADD => 'onPostSave',
            DataObjectEvents::PRE_UPDATE => 'onPostSave',
        ];
    }

    public function onPostSave(DataObjectEvent $event): void
    {
        if (!$this->settings->isSortingEnabled()) {
            return;
        }

        if (!class_exists(Post::class)) {
            return;
        }

        $object = $event->getObject();
        if (!$object instanceof Post) {
            return;
        }

        $this->organizer->assignParentForDate($object);
    }
}
