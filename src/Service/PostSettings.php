<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\Service;

final class PostSettings
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function getPostRootFolder(): string
    {
        return (string) ($this->config['post_root_folder'] ?? '/posts');
    }

    public function isSortingEnabled(): bool
    {
        return (bool) ($this->config['sorting']['enabled'] ?? false);
    }

    public function getSortingDateField(): string
    {
        return (string) ($this->config['sorting']['date_field'] ?? 'date');
    }

    public function getArchiveIdleMinutes(): int
    {
        return (int) ($this->config['archive']['idle_minutes'] ?? 10);
    }
}
