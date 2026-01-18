<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Command;

use InSquare\PimcorePostBundle\Archive\ArchiveChangeTracker;
use InSquare\PimcorePostBundle\Archive\ArchiveRebuilder;
use InSquare\PimcorePostBundle\Service\PostSettings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'insquare:post-archive:rebuild',
    description: 'Rebuilds post archive tables (category + tag).'
)]
final class RebuildPostArchiveCommand extends Command
{
    public function __construct(
        private ArchiveRebuilder $rebuilder,
        private ArchiveChangeTracker $changeTracker,
        private PostSettings $settings
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Rebuild archive without idle-time check.')
            ->addOption('idle-minutes', null, InputOption::VALUE_REQUIRED, 'Idle minutes required since last change.', (string) $this->settings->getArchiveIdleMinutes());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');
        $idleMinutes = (int) $input->getOption('idle-minutes');

        if (!$force) {
            $lastChange = $this->changeTracker->getLastChangeTimestamp();
            if (null === $lastChange) {
                $io->note('No changes detected. Skipping rebuild.');
                return Command::SUCCESS;
            }

            $idleSeconds = max(1, $idleMinutes) * 60;
            if ((time() - $lastChange) < $idleSeconds) {
                $io->note(sprintf(
                    'Last change was less than %d minutes ago. Skipping rebuild.',
                    $idleMinutes
                ));
                return Command::SUCCESS;
            }
        }

        try {
            $result = $this->rebuilder->rebuild();
        } catch (\Throwable $exception) {
            $io->error('Archive rebuild failed: ' . $exception->getMessage());
            return Command::FAILURE;
        }

        if (!$result->isSuccess()) {
            $io->error('Archive rebuild skipped: Post class is not available.');
            return Command::FAILURE;
        }

        $this->changeTracker->clear();

        $io->success(sprintf(
            'Archive rebuilt. Posts: %d, category rows: %d, tag rows: %d, global buckets: %d.',
            $result->getProcessedPosts(),
            $result->getCategoryRows(),
            $result->getTagRows(),
            $result->getGlobalBuckets()
        ));

        return Command::SUCCESS;
    }
}
