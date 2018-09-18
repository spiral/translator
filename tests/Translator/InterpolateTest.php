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
use Spiral\Translator\Bootloader\TranslatorBootloader;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\Catalogue\StaticLoader;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class InterpolateTest extends TestCase
{
    public function testInterpolate()
    {
        $this->assertSame(
            "Welcome, Antony!",
            $this->translator()->trans("Welcome, Antony!", ['name' => 'Antony'])
        );
    }

    public function testInterpolateNumbers()
    {
        $this->assertSame(
            "Bye, Antony!",
            $this->translator()->trans("Bye, Antony!", ['Antony'])
        );
    }

    public function testInterpolateBad()
    {
        $this->assertSame(
            "Bye, {1}!",
            $this->translator()->trans("Bye, {1}!", [new self])
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
                "Welcome, {name}!" => "Welcome, {name}!",
                "Bye, {1}!"        => "Bye, {1}!"
            ]
        ]));

        $container->bind(LoaderInterface::class, $loader);

        return $container->get(TranslatorInterface::class);
    }
}