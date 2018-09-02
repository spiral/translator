<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\Loaders\PhpFileLoader;
use Symfony\Component\Translation\MessageCatalogue;

class PHPLoaderTest extends TestCase
{
    public function testLoader()
    {
        $loader = new PhpFileLoader();

        $catalogue = $loader->load(__DIR__ . '/fixtures/ru.php', 'ru');

        $this->assertInstanceOf(MessageCatalogue::class, $catalogue);
        $this->assertSame('translation', $catalogue->get('message'));
    }
}