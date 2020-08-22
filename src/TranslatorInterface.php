<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator;

/**
 * Spiral translation built at top of symfony Translator and provides ability to route multiple
 * string sets (bundles) into one bigger domain. Such technique provides ability to collect and
 * generate location files based on application source without extra code.
 */
interface TranslatorInterface extends \Symfony\Contracts\Translation\TranslatorInterface
{
    /**
     * Default set of braces to be used in classes or views for indication of translatable content.
     */
    public const I18N_PREFIX  = '[[';
    public const I18N_POSTFIX = ']]';

    /**
     * Currently active locale.
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Resolve domain name for given bundle.
     *
     * @param string $bundle
     * @return string
     */
    public function getDomain(string $bundle): string;

    /**
     * Get associated catalogue manager.
     *
     * @return CatalogueManagerInterface
     */
    public function getCatalogueManager(): CatalogueManagerInterface;
}
