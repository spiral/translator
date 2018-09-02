<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Core\MemoryInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Loaders\PhpFileLoader;
use Spiral\Translator\Locales\LocaleManager;
use Symfony\Component\Translation\Loader\PoFileLoader;

class LocalesTest extends TestCase
{
    public function testLocalesFromLoader()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->andReturn(null);
        $memory->shouldReceive('saveData')->andReturn(null);

        $manager = new LocaleManager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $this->assertTrue($manager->has('ru'));
        $this->assertTrue($manager->has('en'));
    }

    public function testLocalesFromMemory()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->andReturn(['en', 'ru']);
        $memory->shouldNotReceive('saveData')->andReturn(null);

        $manager = new LocaleManager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $this->assertTrue($manager->has('ru'));
        $this->assertTrue($manager->has('en'));
    }
}