<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Locales;

use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Configs\TranslatorConfig;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CatalogueLoader
{
    use LoggerTrait;

    /** @var TranslatorConfig */
    private $config = null;

    /**
     * @param TranslatorConfig $config
     */
    public function __construct(TranslatorConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Check if locale data exists.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function hasLocale(string $locale): bool
    {
        $locale = preg_replace("/[^a-zA-Z_]/", '', mb_strtolower($locale));

        return is_dir($this->config->localeDirectory($locale));
    }

    /**
     * List of all known locales.
     *
     * @return array
     */
    public function getLocales(): array
    {
        $finder = new Finder();
        $finder->in($this->config->localesDirectory())->directories();

        $locales = [];

        /**
         * @var \Symfony\Component\Finder\SplFileInfo $directory
         */
        foreach ($finder->directories()->getIterator() as $directory) {
            $locales[] = $directory->getFilename();
        }

        return $locales;
    }

    /**
     * @param string $locale
     *
     * @return Catalogue
     */
    public function loadCatalogue(string $locale): Catalogue
    {
        $catalogue = new Catalogue($locale);

        $finder = new Finder();
        $finder->in($this->config->localeDirectory($locale));

        /**
         * @var SplFileInfo $file
         */
        foreach ($finder->getIterator() as $file) {
            $this->getLogger()->info(
                sprintf("found locale domain file '{file}'", $file->getFilename()),
                ['file' => $file->getFilename()]
            );

            //Per application agreement domain name must present in filename
            $domain = strstr($file->getFilename(), '.', true);

            if (!$this->config->hasLoader($file->getExtension())) {
                $this->getLogger()->warning(
                    sprintf(
                        "unable to load domain file '{file}', no loader found",
                        $file->getFilename()
                    ),
                    ['file' => $file->getFilename()]
                );

                continue;
            }

            $catalogue->mergeFrom(
                $this->config->getLoader($file->getExtension())->load(
                    (string)$file,
                    $locale,
                    $domain
                )
            );
        }

        return $catalogue;
    }
}