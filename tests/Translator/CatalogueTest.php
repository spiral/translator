<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\Catalogue;
use Symfony\Component\Translation\MessageCatalogue;

class CatalogueTest extends TestCase
{
    public function testGetLocale()
    {
        $catalogue = new Catalogue('ru', []);

        $this->assertSame('ru', $catalogue->getLocale());
        $this->assertSame([], $catalogue->getData());
    }

    public function testHas()
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation'
            ],
            'views'    => [
                'view' => 'Russian View'
            ]
        ]);

        $this->assertSame(['messages', 'views'], $catalogue->getDomains());

        $this->assertTrue($catalogue->has('messages', 'message'));
        $this->assertTrue($catalogue->has('views', 'view'));
        $this->assertFalse($catalogue->has('messages', 'another-message'));
        $this->assertFalse($catalogue->has('other-domain', 'message'));
    }

    /**
     * @expectedException \Spiral\Translator\Exception\CatalogueException
     * @expectedExceptionMessage Undefined string in domain 'domain'
     */
    public function testUndefinedString()
    {
        $catalogue = new Catalogue('ru', []);
        $catalogue->get('domain', 'message');
    }

    public function testLoadAndGet()
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation'
            ],
            'views'    => [
                'view' => 'Russian View'
            ]
        ]);

        $this->assertSame('Russian Translation', $catalogue->get('messages', 'message'));
        $this->assertSame('Russian View', $catalogue->get('views', 'view'));
    }

    public function testLoadGetAndSet()
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation'
            ],
            'views'    => [
                'view' => 'Russian View'
            ]
        ]);

        $this->assertSame('Russian Translation', $catalogue->get('messages', 'message'));
        $this->assertSame('Russian View', $catalogue->get('views', 'view'));

        $this->assertFalse($catalogue->has('views', 'message'));
        $catalogue->set('views', 'message', 'View Message');
        $this->assertTrue($catalogue->has('views', 'message'));

        $this->assertSame('View Message', $catalogue->get('views', 'message'));

        $this->assertSame([
            'messages' => [
                'message' => 'Russian Translation'
            ],
            'views'    => [
                'view'    => 'Russian View',
                'message' => 'View Message'
            ]
        ], $catalogue->getData());
    }

    public function testMergeSymfonyAndFollow()
    {
        $catalogue = new Catalogue('ru', []);

        $catalogue->set('domain', 'message', 'Original Translation');
        $this->assertSame('Original Translation', $catalogue->get('domain', 'message'));

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, true);

        $this->assertSame('Original Translation', $catalogue->get('domain', 'message'));
    }

    public function testMergeSymfonyAndFollowOnEmpty()
    {
        $catalogue = new Catalogue('ru', []);

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, true);

        $this->assertSame('Translation', $catalogue->get('domain', 'message'));
    }

    public function testMergeSymfonyAndReplace()
    {
        $catalogue = new Catalogue('ru', []);

        $catalogue->set('domain', 'message', 'Original Translation');
        $this->assertSame('Original Translation', $catalogue->get('domain', 'message'));

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, false);

        $this->assertSame('Translation', $catalogue->get('domain', 'message'));
    }

    public function testToCatalogue()
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation'
            ],
            'views'    => [
                'view' => 'Russian View'
            ]
        ]);

        $sc = $catalogue->toMessageCatalogue();

        $this->assertSame("ru", $sc->getLocale());
        $this->assertSame(['messages', 'views'], $sc->getDomains());
    }
}