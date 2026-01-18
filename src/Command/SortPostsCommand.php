<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Command;

use InSquare\PimcorePostBundle\Service\PostFolderOrganizer;
use InSquare\PimcorePostBundle\Service\PostSettings;
use Pimcore\Model\DataObject\Post;
use Pimcore\Model\DataObject\Post\Listing as PostListing;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'insquare:post:sort',
    description: 'Sorts all Post objects into date-based folders.'
)]
final class SortPostsCommand extends Command
{
    public function __construct(
        private PostFolderOrganizer $organizer,
        private PostSettings $settings
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!class_exists(Post::class) || !class_exists(PostListing::class)) {
            $io->error('Post class is not available.');
            return Command::FAILURE;
        }

        if (!$this->settings->isSortingEnabled()) {
            $io->warning('Sorting is disabled in config, but the command will continue.');
        }

        $listing = new PostListing();
        $listing->setUnpublished(false);

        $moved = 0;
        $skipped = 0;
        $missingDate = 0;

        foreach ($listing as $post) {
            if (!$post instanceof Post) {
                continue;
            }

            $date = $this->organizer->resolveDate($post);
            if (!$date instanceof \DateTimeInterface) {
                $missingDate++;
                continue;
            }

            $result = $this->organizer->moveToDateFolder($post, $date);
            if ($result) {
                $moved++;
            } else {
                $skipped++;
            }
        }

        $io->success(sprintf(
            'Sorting finished. Moved: %d, skipped: %d, missing date: %d.',
            $moved,
            $skipped,
            $missingDate
        ));

        return Command::SUCCESS;
    }
}
