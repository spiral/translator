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
use Spiral\Tokenizer\Bootloaders\TokenizerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Configs\TokenizerConfig;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Indexer;
use Spiral\Translator\Traits\TranslatorTrait;

class IndexerTest extends TestCase
{
    use TranslatorTrait;

    const MESSAGES = [
        '[[indexer-message]]',
        'not-message'
    ];

    public function testIndexShortFunctions()
    {
        $catalogue = new Catalogue('en');
        $indexer = new Indexer(new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]), $catalogue);

        $indexer->indexInvocations($this->tContainer()->get(InvocationsInterface::class));

        $this->assertTrue($catalogue->has('messages', 'hello'));
        $this->assertTrue($catalogue->has('messages', '{n} dog|{n} dogs'));

        $this->assertTrue($catalogue->has('spiral', 'other'));
        $this->assertTrue($catalogue->has('spiral', 'hi-from-class'));
    }

    public function testIndexClasses()
    {
        $catalogue = new Catalogue('en');
        $indexer = new Indexer(new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]), $catalogue);

        $indexer->indexClasses($this->tContainer()->get(ClassesInterface::class));

        $this->assertTrue($catalogue->has('spiral', 'indexer-message'));
        $this->assertFalse($catalogue->has('spiral', 'not-message'));

        // from stubs
        $this->assertTrue($catalogue->has('spiral', 'some-text'));
        $this->assertFalse($catalogue->has('spiral', 'no-message'));

        $this->assertTrue($catalogue->has('spiral', 'new-mess'));
    }

    private function inner()
    {
        $var = 'something';
        l($var);

        l('other', [], 'spiral-domain');
        l('hello');
        p('{n} dog|{n} dogs', 1);
        p('{n} cat|{n} cats', 1, [], 'spiral-domain');
    }

    protected function tContainer(): Container
    {
        $container = new Container();
        $bootloader = new BootloadManager($container);
        $bootloader->bootload([TokenizerBootloader::class]);

        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        return $container;
    }
}