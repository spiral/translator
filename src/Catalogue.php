<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Translator;

use Spiral\Translator\Exceptions\CatalogueException;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Similar to Symfony catalogue, however this one does not operate with fallback locale.
 * Provides ability to cache domains in memory.
 */
class Catalogue implements CatalogueInterface
{
    /** @var string */
    private $locale;

    /** @var array */
    private $data = [];

    /**
     * @param string $locale
     * @param array  $data
     */
    public function __construct(string $locale, array $data = [])
    {
        $this->locale = $locale;
        $this->data = $data;
    }

    /**
     * Check if domain message exists.
     *
     * @param string $domain
     * @param string $string
     *
     * @return bool
     */
    public function has(string $domain, string $string): bool
    {
        if (!empty($this->data[$domain]) && array_key_exists($string, $this->data[$domain])) {
            return true;
        }

        return array_key_exists($string, $this->data[$domain]);
    }

    /**
     * Get domain message.
     *
     * @param string $domain
     * @param string $string
     *
     * @return string
     *
     * @throws CatalogueException
     */
    public function get(string $domain, string $string): string
    {
        if (!$this->has($domain, $string)) {
            throw new CatalogueException("Undefined string in domain '{$domain}'");
        }

        return $this->data[$domain][$string];
    }

    /**
     * Adding string association to be stored into memory.
     *
     * @param string $domain
     * @param string $string
     * @param string $value
     */
    public function set(string $domain, string $string, string $value)
    {
        $this->data[$domain][$string] = $value;
    }

    /**
     * List of loaded domains
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param MessageCatalogue $catalogue
     * @param bool             $follow When set to true messages from given catalogue will overwrite
     *                                 existed messages.
     */
    public function mergeFrom(MessageCatalogue $catalogue, bool $follow = true)
    {
        foreach ($catalogue->all() as $domain => $messages) {
            if (!isset($this->data[$domain])) {
                $this->data[$domain] = [];
            }

            if ($follow) {
                //MessageCatalogue string has higher priority that string stored in memory
                $this->data[$domain] = array_merge($messages, $this->data[$domain]);
            } else {
                $this->data[$domain] = array_merge($this->data[$domain], $messages);
            }
        }
    }

    /**
     * Converts into one MessageCatalogue.
     *
     * @return MessageCatalogue
     */
    public function toMessageCatalogue(): MessageCatalogue
    {
        return new MessageCatalogue($this->locale, $this->data);
    }
}