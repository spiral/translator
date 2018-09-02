<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Locales;

use Spiral\Core\MemoryInterface;
use Spiral\Translator\Catalogue;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Exceptions\LocaleException;
use Spiral\Translator\LocalesInterface;

/**
 * Manages catalogues and their cached data.
 */
class LocaleManager implements LocalesInterface
{
    const MEMORY = "locales";

    /** @var TranslatorConfig */
    private $config = null;

    /** @var \Spiral\Translator\Locales\CatalogueLoader */
    private $loader;

    /** @var MemoryInterface */
    private $memory = null;

    /** @var array */
    private $locales = [];

    /** @var Catalogue[] */
    private $catalogues = [];

    /**
     * @param TranslatorConfig $config
     * @param MemoryInterface  $memory
     */
    public function __construct(TranslatorConfig $config, MemoryInterface $memory)
    {
        $this->config = $config;
        $this->loader = new CatalogueLoader($config);
        $this->memory = $memory;
    }

    /**
     * @inheritdoc
     */
    public function getNames(): array
    {
        if (!empty($this->locales)) {
            return $this->locales;
        }

        $this->locales = (array)$this->memory->loadData(self::MEMORY);
        if (empty($this->locales)) {
            $this->locales = $this->loader->getLocales();
        }
        $this->memory->saveData(self::MEMORY, $this->locales);

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
            throw new LocaleException(sprintf("Undefined locale `%s`.", $locale));
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
            $this->get($locale)
        );
    }

    /**
     * @inheritdoc
     */
    public function has(string $locale): bool
    {
        return isset($this->catalogues[$locale]) || in_array($locale, $this->getNames());
    }

    /**
     * @inheritdoc
     */
    public function get(string $locale): CatalogueInterface
    {
        return $this->load($locale);
    }
}