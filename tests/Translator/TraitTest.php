<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\BootloadManager;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\MemoryInterface;
use Spiral\Core\NullMemory;
use Spiral\Translator\Bootloaders\TranslatorBootloader;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Loaders\PhpFileLoader;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Loader\PoFileLoader;

class TraitTest extends TestCase
{
    use TranslatorTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();

        $this->container->bind(MemoryInterface::class, new NullMemory());
        $this->container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'    => 'en',
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
            'domains'   => [
                'messages' => ['*']
            ]
        ]));

        $bootloader = new BootloadManager($this->container);
        $bootloader->bootload([TranslatorBootloader::class]);
    }

    public function testScopeException()
    {
        $this->assertSame("message", $this->say("message"));
    }

    public function testTranslate()
    {
        ContainerScope::runScope($this->container, function () {
            $this->assertSame("message", $this->say("message"));
        });

        $this->container->get(TranslatorInterface::class)->setLocale('ru');

        ContainerScope::runScope($this->container, function () {
            $this->assertSame("translation", $this->say("message"));
        });

        ContainerScope::runScope($this->container, function () {
            $this->assertSame("translation", $this->say("[[message]]"));
        });
    }
}