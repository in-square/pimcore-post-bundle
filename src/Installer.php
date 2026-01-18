<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle;

use Doctrine\DBAL\Connection;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Layout\Panel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class Installer extends SettingsStoreAwareInstaller
{
    private const CLASS_NAME_CATEGORY = 'PostCategory';
    private const CLASS_NAME_TAG = 'PostTag';
    private const CLASS_NAME_POST = 'Post';

    public function __construct(
        private readonly Connection $connection,
        BundleInterface $bundle
    ) {
        parent::__construct($bundle);
    }

    public function install(): void
    {
        $this->installClassDefinitions();
        $this->createArchiveTables();
        parent::install();
    }

    private function installClassDefinitions(): void
    {
        $this->installCategoryClassDefinition();
        $this->installTagClassDefinition();
        $this->installPostClassDefinition();
    }

    private function installCategoryClassDefinition(): void
    {
        if (ClassDefinition::getByName(self::CLASS_NAME_CATEGORY)) {
            return;
        }

        $rootPanel = new Panel();
        $rootPanel->setName('pimcore_root');

        $mainPanel = new Panel();
        $mainPanel->setName('PostCategory');
        $mainPanel->setTitle('Post Category');

        $title = (new Data\Input())
            ->setName('title')
            ->setTitle('Title')
            ->setMandatory(true)
            ->setColumnLength(255);
        $title->setUnique(true);
        $title->setIndex(true);

        $mainPanel->setChildren([$title]);
        $rootPanel->setChildren([$mainPanel]);

        $class = new ClassDefinition();
        $class->setName(self::CLASS_NAME_CATEGORY);
        $class->setId('post_category');
        $class->setTitle('Post Category');
        $class->setGroup('Post');
        $class->setLayoutDefinitions($rootPanel);
        $class->save();
    }

    private function installTagClassDefinition(): void
    {
        if (ClassDefinition::getByName(self::CLASS_NAME_TAG)) {
            return;
        }

        $rootPanel = new Panel();
        $rootPanel->setName('pimcore_root');

        $mainPanel = new Panel();
        $mainPanel->setName('PostTag');
        $mainPanel->setTitle('Post Tag');

        $title = (new Data\Input())
            ->setName('title')
            ->setTitle('Title')
            ->setMandatory(true)
            ->setColumnLength(255);
        $title->setUnique(true);
        $title->setIndex(true);

        $mainPanel->setChildren([$title]);
        $rootPanel->setChildren([$mainPanel]);

        $class = new ClassDefinition();
        $class->setName(self::CLASS_NAME_TAG);
        $class->setId('post_tag');
        $class->setTitle('Post Tag');
        $class->setGroup('Post');
        $class->setLayoutDefinitions($rootPanel);
        $class->save();
    }

    private function installPostClassDefinition(): void
    {
        if (ClassDefinition::getByName(self::CLASS_NAME_POST)) {
            return;
        }

        $rootPanel = new Panel();
        $rootPanel->setName('pimcore_root');

        $mainPanel = new Panel();
        $mainPanel->setName('Post');
        $mainPanel->setTitle('Post');

        $title = (new Data\Input())
            ->setName('title')
            ->setTitle('Title')
            ->setMandatory(true)
            ->setColumnLength(255);

        $date = (new Data\Date())
            ->setName('date')
            ->setTitle('Date')
            ->setMandatory(true);
        $date->setColumnType('date');
        $date->setUseCurrentDate(false);

        $categories = (new Data\ManyToManyObjectRelation())
            ->setName('categories')
            ->setTitle('Categories')
            ->setClasses([['classes' => self::CLASS_NAME_CATEGORY]])
            ->setVisibleFields('title');

        $tags = (new Data\ManyToManyObjectRelation())
            ->setName('tags')
            ->setTitle('Tags')
            ->setClasses([['classes' => self::CLASS_NAME_TAG]])
            ->setVisibleFields('title');

        $mainPanel->setChildren([$title, $date, $categories, $tags]);
        $rootPanel->setChildren([$mainPanel]);

        $class = new ClassDefinition();
        $class->setName(self::CLASS_NAME_POST);
        $class->setId('post');
        $class->setTitle('Post');
        $class->setGroup('Post');
        $class->setLayoutDefinitions($rootPanel);
        $class->save();
    }

    private function createArchiveTables(): void
    {
        $this->connection->executeStatement(
            'CREATE TABLE IF NOT EXISTS post_archive_by_category (
                id INT AUTO_INCREMENT NOT NULL,
                category_id INT DEFAULT NULL,
                year INT NOT NULL,
                month INT NOT NULL,
                count INT NOT NULL,
                UNIQUE INDEX uniq_post_archive_by_category (category_id, year, month),
                INDEX idx_post_archive_by_category_year_month (year, month),
                INDEX idx_post_archive_by_category_category (category_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARSET=utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        $this->connection->executeStatement(
            'CREATE TABLE IF NOT EXISTS post_archive_by_tag (
                id INT AUTO_INCREMENT NOT NULL,
                tag_id INT NOT NULL,
                year INT NOT NULL,
                month INT NOT NULL,
                count INT NOT NULL,
                UNIQUE INDEX uniq_post_archive_by_tag (tag_id, year, month),
                INDEX idx_post_archive_by_tag_year_month (year, month),
                INDEX idx_post_archive_by_tag_tag (tag_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARSET=utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
    }
}
