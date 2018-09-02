<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Translator\Catalogues\CatalogueManager;
use Spiral\Translator\LocalesInterface;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class TranslatorBootloader extends Bootloader
{
    const SINGLETONS = [
        TranslatorInterface::class => Translator::class,
        LocalesInterface::class    => CatalogueManager::class
    ];
}