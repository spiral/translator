<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Translator\Catalogue\Loader;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\Catalogue\Manager;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class TranslatorBootloader extends Bootloader
{
    const SINGLETONS = [
        TranslatorInterface::class => Translator::class,
        CataloguesInterface::class => Manager::class,
    ];

    const BINDINGS = [
        LoaderInterface::class => Loader::class
    ];
}