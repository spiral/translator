<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Exceptions\LocaleException;
use Spiral\Translator\Exceptions\PluralizationException;
use Symfony\Component\Translation\MessageSelector;

/**
 * Implementation of Symfony\TranslatorInterface with memory caching, automatic message
 * registration and bundle/domain grouping.
 */
class Translator implements TranslatorInterface, SingletonInterface
{
    /** @var TranslatorConfig */
    private $config;

    /** @var string */
    private $locale = '';

    /**
     * @invisible
     * @var MessageSelector
     */
    private $selector;

    /** @var CataloguesInterface */
    private $catalogues;

    /**
     * @param TranslatorConfig    $config
     * @param MessageSelector     $selector
     * @param CataloguesInterface $locales
     */
    public function __construct(
        TranslatorConfig $config,
        MessageSelector $selector,
        CataloguesInterface $locales
    ) {
        $this->config = $config;
        $this->selector = $selector;
        $this->catalogues = $locales;

        $this->setLocale($this->config->defaultLocale());
    }

    /**
     * @inheritdoc
     */
    public function resolveDomain(string $bundle): string
    {
        return $this->config->resolveDomain($bundle);
    }

    /**
     * @inheritdoc
     *
     * @return $this
     *
     * @throws LocaleException
     */
    public function setLocale($locale)
    {
        if (!$this->catalogues->has($locale)) {
            throw new LocaleException($locale);
        }

        $this->locale = $locale;
        $this->catalogues->load($locale);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function getCatalogues(): CataloguesInterface
    {
        return $this->catalogues;
    }

    /**
     * {@inheritdoc}
     *
     * Parameters will be embedded into string using { and } braces.
     *
     * @throws LocaleException
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->config->defaultDomain();
        $locale = $locale ?? $this->locale;

        $message = $this->get($locale, $domain, $id);

        return $this->interpolate($message, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * Default symfony pluralizer to be used. Parameters will be embedded into string using { and }
     * braces. In addition you can use forced parameter {n} which contain formatted number value.
     *
     * @throws LocaleException
     * @throws PluralizationException
     */
    public function transChoice(
        $id,
        $number,
        array $parameters = [],
        $domain = null,
        $locale = null
    ) {
        $domain = $domain ?? $this->config->defaultDomain();
        $locale = $locale ?? $this->locale;

        if (empty($parameters['n'])) {
            $parameters['n'] = number_format($number);
        }

        try {
            $message = $this->get($locale, $domain, $id);

            $pluralized = $this->selector->choose(
                $message,
                $number,
                $locale
            );
        } catch (\InvalidArgumentException $e) {
            //Wrapping into more explanatory exception
            throw new PluralizationException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->interpolate($pluralized, $parameters);
    }

    /**
     * Get translation message from the locale bundle or fallback to default locale.
     *
     * @param string $locale
     * @param string $domain
     * @param string $string
     *
     * @return string
     */
    protected function get(string &$locale, string $domain, string $string): string
    {
        if ($this->catalogues->get($locale)->has($domain, $string)) {
            return $this->catalogues->get($locale)->get($domain, $string);
        }

        $locale = $this->config->fallbackLocale();

        if ($this->catalogues->get($locale)->has($domain, $string)) {
            return $this->catalogues->get($locale)->get($domain, $string);
        }

        // we can automatically register message
        if ($this->config->registerMessages()) {
            $this->catalogues->get($locale)->set($domain, $string, $string);
            $this->catalogues->save($locale);
        }

        //Unable to find translation
        return $string;
    }

    /**
     * Interpolate string with given parameters, used by many spiral components.
     *
     * Input: Hello {name}! Good {time}! + ['name' => 'Member', 'time' => 'day']
     * Output: Hello Member! Good Day!
     *
     * @param string $string
     * @param array  $values  Arguments (key => value). Will skip unknown names.
     * @param string $prefix  Placeholder prefix, "{" by default.
     * @param string $postfix Placeholder postfix, "}" by default.
     *
     * @return string
     */
    protected function interpolate(
        string $string,
        array $values,
        string $prefix = '{',
        string $postfix = '}'
    ): string {
        $replaces = [];
        foreach ($values as $key => $value) {
            $value = (is_array($value) || $value instanceof \Closure) ? '' : $value;
            try {
                //Object as string
                $value = is_object($value) ? (string)$value : $value;
            } catch (\Exception $e) {
                $value = '';
            }
            $replaces[$prefix . $key . $postfix] = $value;
        }

        return strtr($string, $replaces);
    }

    /**
     * Check if string has translation braces [[ and ]].
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isMessage(string $string): bool
    {
        return substr($string, 0, 2) == self::I18N_PREFIX
            && substr($string, -2) == self::I18N_POSTFIX;
    }
}