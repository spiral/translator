<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Catalogue;

use Spiral\Core\MemoryInterface;
use Spiral\Translator\Catalogue;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Exception\LocaleException;

/**
 * Manages catalogues and their cached data.
 */
final class CatalogueManager implements CataloguesInterface
{
    const MEMORY = "locales";

    /** @var LoaderInterface */
    private $loader;

    /**
     * @invisible
     * @var MemoryInterface
     */
    private $memory = null;

    /** @var array */
    private $locales = [];

    /** @var Catalogue[] */
    private $catalogues = [];

    /**
     * @param LoaderInterface $loader
     * @param MemoryInterface $memory
     */
    public function __construct(LoaderInterface $loader, MemoryInterface $memory)
    {
        $this->loader = $loader;
        $this->memory = $memory;
    }

    /**
     * @inheritdoc
     */
    public function getLocales(): array
    {
        if (!empty($this->locales)) {
            return $this->locales;
        }

        $this->locales = (array)$this->memory->loadData(self::MEMORY);
        if (empty($this->locales)) {
            $this->locales = $this->loader->getLocales();
            $this->memory->saveData(self::MEMORY, $this->locales);
        }

        return $this->locales;
    }

    /**
     * @inheritdoc
     */
    public function load(string $locale): CatalogueInterface
    {
        if (isset($this->catalogues[$locale])) {
            return $this->catalogues[$locale];
        }

        if (!$this->has($locale)) {
            throw new LocaleException($locale);
        }

        $data = (array)$this->memory->loadData(sprintf("%s/%s", self::MEMORY, $locale));
        if (!empty($data)) {
            $this->catalogues[$locale] = new Catalogue($locale, $data);
        } else {
            $this->catalogues[$locale] = $this->loader->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }

    /**
     * @inheritdoc
     */
    public function save(string $locale)
    {
        $this->memory->saveData(
            sprintf("%s/%s", self::MEMORY, $locale),
            $this->get($locale)->getData()
        );
    }

    /**
     * @inheritdoc
     */
    public function has(string $locale): bool
    {
        return isset($this->catalogues[$locale]) || in_array($locale, $this->getLocales());
    }

    /**
     * @inheritdoc
     */
    public function get(string $locale): CatalogueInterface
    {
        return $this->load($locale);
    }

    /**
     * Reset all cached data and loaded locates.
     */
    public function reset()
    {
        $this->memory->saveData(self::MEMORY, null);
        foreach ($this->getLocales() as $locale) {
            $this->memory->saveData(sprintf("%s/%s", self::MEMORY, $locale), null);
        }

        $this->locales = [];
        $this->catalogues = [];
    }
}