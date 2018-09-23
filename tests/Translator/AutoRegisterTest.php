<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\MemoryInterface;
use Spiral\Core\NullMemory;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\Catalogue\StaticLoader;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class AutoRegisterTest extends TestCase
{
    public function testRegister()
    {
        $tr = $this->translator();

        $this->assertTrue($tr->getCatalogues()->get('en')->has('messages', 'Welcome, {name}!'));
        $this->assertFalse($tr->getCatalogues()->get('en')->has('messages', 'new'));

        $tr->trans('new');
        $this->assertTrue($tr->getCatalogues()->get('en')->has('messages', 'new'));
    }

    protected function translator(): Translator
    {
        $container = new Container();
        $container->bind(MemoryInterface::class, new NullMemory());
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'       => 'en',
            'autoRegister' => true,
            'domains'      => [
                'messages' => ['*']
            ]
        ]));

        $container->bindSingleton(TranslatorInterface::class, Translator::class);
        $container->bindSingleton(CataloguesInterface::class, Catalogue\CatalogueManager::class);
        $container->bind(LoaderInterface::class, Catalogue\CatalogueLoader::class);

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