# Pimcore Post Bundle

Bundle provides basic blog data objects (Post, PostCategory, PostTag), archive tables for monthly counts, and tools for organizing posts by date.

## Features
- Installs `PostCategory`, `PostTag`, and `Post` classes (with `date` field).
- Creates archive tables: `post_archive_by_category`, `post_archive_by_tag`.
- Event-based change tracking + CRON-friendly archive rebuild.
- Date-based folder sorting for existing posts.

## Requirements
- PHP 8.3
- Symfony 6.4
- Pimcore 11

## Installation
Install the bundle via Composer and run the Pimcore installer:

```bash
composer require in-square/pimcore-post-bundle
php bin/console pimcore:bundle:install InSquarePimcorePostBundle
```

## Configuration
Create `config/packages/in_square_pimcore_post.yaml` in the Pimcore project:

```yaml
in_square_pimcore_post:
    post_root_folder: '/posts'
    sorting:
        enabled: true
        date_field: 'date'
    archive:
        idle_minutes: 10
```

## Archive rebuild (CRON)
Event subscriber stores `last_posts_change` in cache. Run rebuild command via CRON:

```bash
* * * * * /usr/bin/php /path/to/pimcore/bin/console insquare:post-archive:rebuild
```

Command supports `--force` and `--idle-minutes`:

```bash
php bin/console insquare:post-archive:rebuild --force
```

## Sorting existing posts
Use this command to move existing posts into `/Y/m/d` folders:

```bash
php bin/console insquare:post:sort
```

## Development
Static analysis and coding standards:

```bash
composer phpstan
composer php-cs
composer php-cs:dry
```
