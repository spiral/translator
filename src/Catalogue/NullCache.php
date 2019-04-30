<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Catalogue;

final class NullCache implements CacheInterface
{
    /**
     * @inheritdoc
     */
    public function setLocales(?array $locales)
    {
    }

    /**
     * @inheritdoc
     */
    public function getLocales(): ?array
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function saveLocale(string $locale, ?array $data)
    {
    }

    /**
     * @inheritdoc
     */
    public function loadLocale(string $locale): ?array
    {
        return null;
    }
}