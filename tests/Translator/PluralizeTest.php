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
use Spiral\Translator\Catalogue;
use Spiral\Translator\Catalogues\LoaderInterface;
use Spiral\Translator\Catalogues\StaticLoader;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class PluralizeTest extends TestCase
{
    public function testPluralize()
    {
        $this->assertSame(
            '1 dog',
            $this->translator()->transChoice("{n} dog|{n} dogs", 1)
        );

        $this->assertSame(
            '2 dogs',
            $this->translator()->transChoice("{n} dog|{n} dogs", 2)
        );

        $this->assertSame(
            '2,220 dogs',
            $this->translator()->transChoice("{n} dog|{n} dogs", 2220)
        );

        $this->assertSame(
            '100 dogs',
            $this->translator()->transChoice("{n} dog|{n} dogs", 10, [
                'n' => 100
            ])
        );
    }

    public function testRussian()
    {
        $tr = $this->translator();
        $tr->setLocale('ru');

        $this->assertSame(
            '1 собака',
            $tr->transChoice("{n} dog|{n} dogs", 1)
        );

        $this->assertSame(
            '2 собаки',
            $tr->transChoice("{n} dog|{n} dogs", 2)
        );

        $this->assertSame(
            '8 собак',
            $tr->transChoice("{n} dog|{n} dogs", 8)
        );
    }

    public function testRussianFallback()
    {
        $tr = $this->translator();
        $tr->setLocale('ru');

        $this->assertSame(
            '1 собака',
            $tr->transChoice("{n} dog|{n} dogs", 1)
        );

        $this->assertSame(
            '1 cat',
            $tr->transChoice("{n} cat|{n} cats", 1)
        );

        $this->assertSame(
            '2 cats',
            $tr->transChoice("{n} cat|{n} cats", 2)
        );

        $this->assertSame(
            '8 cats',
            $tr->transChoice("{n} cat|{n} cats", 8)
        );
    }

    protected function translator(): Translator
    {
        $container = new Container();
        $container->bind(MemoryInterface::class, new NullMemory());
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'  => 'en',
            'domains' => [
                'messages' => ['*']
            ]
        ]));

        $bootloader = new BootloadManager($container);
        $bootloader->bootload([TranslatorBootloader::class]);

        $loader = new StaticLoader();
        $loader->addCatalogue('en', new Catalogue('en', [
            'messages' => [
                "{n} dog|{n} dogs" => "{n} dog|{n} dogs",
                "{n} cat|{n} cats" => "{n} cat|{n} cats",
            ]
        ]));

        $loader->addCatalogue('ru', new Catalogue('en', [
            'messages' => [
                "{n} dog|{n} dogs" => "{n} собака|{n} собаки|{n} собак",
            ]
        ]));


        $container->bind(LoaderInterface::class, $loader);

        return $container->get(TranslatorInterface::class);
    }
}