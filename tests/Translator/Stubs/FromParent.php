<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Tests\Stubs;

/**
 * @inherit-messages
 */
class FromParent extends MessageStub
{
    const INHERIT_TRANSLATIONS = true;

    private $other = [
        '[[new-mess]]'
    ];

    protected function hi()
    {
        return $this->say("hi-from-class");
    }
}
