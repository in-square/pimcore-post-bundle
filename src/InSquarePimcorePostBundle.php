<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle;

use InSquare\PimcorePostBundle\DependencyInjection\InSquarePimcorePostExtension;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Installer\InstallerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class InSquarePimcorePostBundle extends AbstractPimcoreBundle
{
    public function getInstaller(): ?InstallerInterface
    {
        return $this->container->get(Installer::class);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new InSquarePimcorePostExtension();
        }

        return $this->extension;
    }
}
