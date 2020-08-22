<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Translator\Exception\TranslatorException;
use Spiral\Translator\TranslatorInterface;

if (!function_exists('l')) {
    /**
     * Translate message using default or specific bundle name.
     *
     * Examples:
     * l('Some Message');
     * l('Hello {name}!', ['name' => $name]);
     *
     * @param string $string
     * @param array  $options
     * @param string $domain
     *
     * @return string
     *
     * @throws TranslatorException
     * @throws ScopeException
     */
    function l(string $string, array $options = [], string $domain = null): string
    {
        $container = ContainerScope::getContainer();
        if (empty($container) || !$container->has(TranslatorInterface::class)) {
            throw new ScopeException(
                '`TranslatorInterface` binding is missing or container scope is not set'
            );
        }

        /** @var TranslatorInterface $translator */
        $translator = $container->get(TranslatorInterface::class);

        return $translator->trans($string, $options, $domain);
    }
}

if (!function_exists('p')) {
    /**
     * Pluralize string using language pluralization options and specified numeric value.
     *
     * Examples:
     * p("{n} user|{n} users", $users);
     *
     * @param string $string Can include {n} as placeholder.
     * @param int    $number
     * @param array  $options
     * @param string $domain
     *
     * @return string
     *
     * @throws TranslatorException
     * @throws ScopeException
     */
    function p(string $string, int $number, array $options = [], string $domain = null): string
    {
        $container = ContainerScope::getContainer();
        if (empty($container) || !$container->has(TranslatorInterface::class)) {
            throw new ScopeException(
                '`TranslatorInterface` binding is missing or container scope is not set'
            );
        }

        /** @var TranslatorInterface $translator */
        $translator = $container->get(TranslatorInterface::class);

        return $translator->trans($string, ['%count%' => $number] + $options, $domain);
    }
}
