<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Catalogues;

use Spiral\Translator\CatalogueInterface;

interface LoaderInterface
{
    /**
     * Check if locale data exists.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function hasLocale(string $locale): bool;

    /**
     * List of all known locales.
     *
     * @return array
     */
    public function getLocales(): array;

    /**
     * @param string $locale
     *
     * @return CatalogueInterface
     */
    public function loadCatalogue(string $locale): CatalogueInterface;
}