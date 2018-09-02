<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Tests\Stubs;

class FromParent extends MessageStub
{
    private $other = [
        '[[new-mess]]'
    ];

    protected function hi()
    {
        return $this->say("hi-from-class");
    }
}
