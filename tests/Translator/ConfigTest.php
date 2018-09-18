<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\Config\TranslatorConfig;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;

class ConfigTest extends TestCase
{
    public function testDefaultLocale()
    {
        $config = new TranslatorConfig([
            'locale' => 'ru'
        ]);

        $this->assertSame('ru', $config->defaultLocale());
    }

    public function testDefaultDomain()
    {
        $config = new TranslatorConfig([
            'locale' => 'ru'
        ]);

        $this->assertSame('messages', $config->defaultDomain());
    }

    public function testFallbackLocale()
    {
        $config = new TranslatorConfig([
            'fallbackLocale' => 'ru'
        ]);

        $this->assertSame('ru', $config->fallbackLocale());
    }

    public function testCacheLocales()
    {
        $config = new TranslatorConfig(['cacheLocales' => true]);
        $this->assertTrue($config->cacheLocales());

        $config = new TranslatorConfig(['cacheLocales' => false]);
        $this->assertFalse($config->cacheLocales());

        //Legacy
        $config = new TranslatorConfig(['autoReload' => true]);
        $this->assertFalse($config->cacheLocales());
    }

    public function testRegisterMessages()
    {
        $config = new TranslatorConfig(['autoRegister' => true]);
        $this->assertTrue($config->registerMessages());

        $config = new TranslatorConfig(['autoRegister' => false]);
        $this->assertFalse($config->registerMessages());

        //Legacy
        $config = new TranslatorConfig(['registerMessages' => true]);
        $this->assertTrue($config->registerMessages());
    }

    public function testLocalesDirectory()
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/'
        ]);

        $this->assertSame('directory/', $config->localesDirectory());
    }

    public function testLocaleDirectory()
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/'
        ]);

        $this->assertSame('directory/ru/', $config->localeDirectory('ru'));
    }

    public function testLocaleDirectoryShort()
    {
        $config = new TranslatorConfig([
            'directory' => 'directory/'
        ]);

        $this->assertSame('directory/ru/', $config->localeDirectory('ru'));
    }

    public function testDomains()
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]);

        $this->assertSame('spiral', $config->resolveDomain('spiral-views'));
        $this->assertSame('messages', $config->resolveDomain('vendor-views'));
    }

    public function testDomainsFallback()
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral' => [
                    'spiral-*'
                ]
            ]
        ]);

        $this->assertSame('external', $config->resolveDomain('external'));
    }

    public function testHasLoader()
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class]
        ]);

        $this->assertTrue($config->hasLoader('php'));
        $this->assertFalse($config->hasLoader('txt'));
    }

    public function testGetLoader()
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class]
        ]);

        $this->assertInstanceOf(LoaderInterface::class, $config->getLoader('php'));
    }

    public function testHasDumper()
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class]
        ]);

        $this->assertTrue($config->hasDumper('po'));
        $this->assertFalse($config->hasDumper('xml'));
    }

    public function testGetDumper()
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class]
        ]);

        $this->assertInstanceOf(DumperInterface::class, $config->getDumper('po'));
    }
}