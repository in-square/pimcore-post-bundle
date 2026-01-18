<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\EventSubscriber;

use InSquare\PimcorePostBundle\Archive\ArchiveChangeTracker;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Post;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PostArchiveChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(private ArchiveChangeTracker $changeTracker)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::POST_ADD => 'onPostChange',
            DataObjectEvents::POST_UPDATE => 'onPostChange',
            DataObjectEvents::POST_DELETE => 'onPostChange',
        ];
    }

    public function onPostChange(DataObjectEvent $event): void
    {
        $object = $event->getObject();

        if (!class_exists(Post::class)) {
            return;
        }

        if (!$object instanceof Post) {
            return;
        }

        $this->changeTracker->markChanged();
    }
}
