<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\BootloadManager;
use Spiral\Core\Container;
use Spiral\Core\MemoryInterface;
use Spiral\Core\NullMemory;
use Spiral\Translator\Bootloaders\TranslatorBootloader;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Loaders\PhpFileLoader;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Loader\PoFileLoader;

class TranslatorTest extends TestCase
{
    public function testIsMessage()
    {
        $this->assertTrue(Translator::isMessage('[[hello]]'));
        $this->assertFalse(Translator::isMessage('hello'));
    }

    public function testLocale()
    {
        $translator = $this->makeTranslator();
        $this->assertSame('en', $translator->getLocale());

        $translator->setLocale('ru');
        $this->assertSame('ru', $translator->getLocale());
    }

    /**
     * @expectedException \Spiral\Translator\Exceptions\LocaleException
     */
    public function testLocaleException()
    {
        $translator = $this->makeTranslator();
        $translator->setLocale('de');
    }

    public function testDomains()
    {
        $translator = $this->makeTranslator();

        $this->assertSame('spiral', $translator->resolveDomain('spiral-views'));
        $this->assertSame('messages', $translator->resolveDomain('vendor-views'));
    }

    public function testCatalogues()
    {
        $translator = $this->makeTranslator();
        $this->assertCount(2, $translator->getCatalogues()->getLocales());
    }

    public function testTrans()
    {
        $translator = $this->makeTranslator();
        $this->assertSame('message', $translator->trans('message'));

        $translator->setLocale('ru');
        $this->assertSame('translation', $translator->trans('message'));
    }

    protected function makeTranslator(): Translator
    {
        $container = new Container();
        $container->bind(MemoryInterface::class, new NullMemory());
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'    => 'en',
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
            'domains'   => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]));

        $bootloader = new BootloadManager($container);
        $bootloader->bootload([TranslatorBootloader::class]);

        return $container->get(TranslatorInterface::class);
    }
}