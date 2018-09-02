<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Translator\Catalogues\Loader;
use Spiral\Translator\Catalogues\LoaderInterface;
use Spiral\Translator\Catalogues\Manager;
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