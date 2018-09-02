<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator;

use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Reflections\ReflectionArgument;
use Spiral\Tokenizer\Reflections\ReflectionInvocation;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Traits\TranslatorTrait;

/**
 * Index available classes and function calls to fetch every used string translation. Can
 * understand l, p and translate (trait) function.
 *
 * In addition Indexer will find every string specified in default value of model or class which
 * uses TranslatorTrait. String has to be embraced with [[ ]] in order to be indexed, you can
 * disable property indexation using @do-not-index doc comment. Translator can merge strings with
 * parent data, set class constant INHERIT_TRANSLATIONS to true.
 */
class Indexer
{
    use LoggerTrait;

    /**
     * @var TranslatorConfig
     */
    private $config;

    /**
     * Catalogue to aggregate messages into.
     *
     * @var Catalogue
     */
    private $catalogue;

    /**
     * @param TranslatorConfig $config
     * @param Catalogue        $catalogue
     */
    public function __construct(TranslatorConfig $config, Catalogue $catalogue)
    {
        $this->config = $config;
        $this->catalogue = $catalogue;
    }

    /**
     * Register string in active translator.
     *
     * @param string $bundle
     * @param string $string
     */
    public function registerMessage(string $bundle, string $string)
    {
        $domain = $this->config->resolveDomain($bundle);

        //Automatically registering
        $this->catalogue->set($domain, $string, $string);

        $this->getLogger()->debug(
            sprintf("[%s]: `%s`", $domain, $string),
            compact('domain', 'string')
        );
    }

    /**
     * Index and register i18n string located in default properties which belongs to TranslatorTrait
     * classes.
     *
     * @param ClassesInterface $locator
     */
    public function indexClasses(ClassesInterface $locator)
    {
        foreach ($locator->getClasses(TranslatorTrait::class) as $class) {
            $strings = $this->fetchMessages(
                $class,
                $class->getConstant('INHERIT_TRANSLATIONS')
            );

            foreach ($strings as $string) {
                $this->registerMessage($class->getName(), $string);
            }
        }
    }

    /**
     * Index available methods and function invocations, target: l, p, $this->translate()
     * functions.
     *
     * @param InvocationsInterface $locator
     */
    public function indexInvocations(InvocationsInterface $locator)
    {
        $this->registerInvocations($locator->getInvocations(
            new \ReflectionFunction('l')
        ));

        $this->registerInvocations($locator->getInvocations(
            new \ReflectionFunction('p')
        ));

        $this->registerInvocations($locator->getInvocations(
            new \ReflectionMethod(TranslatorTrait::class, 'say')
        ));
    }

    /**
     * Register found invocations in translator bundles.
     *
     * @param ReflectionInvocation[] $invocations
     */
    private function registerInvocations(array $invocations)
    {
        foreach ($invocations as $invocation) {
            if ($invocation->getArgument(0)->getType() != ReflectionArgument::STRING) {
                //We can only index invocations with constant string arguments
                continue;
            }

            $string = $invocation->getArgument(0)->stringValue();
            $string = $this->prepareMessage($string);

            $this->registerMessage($this->invocationDomain($invocation), $string);
        }
    }

    /**
     * Fetch default string values from class and merge it with parent strings if requested.
     *
     * @param \ReflectionClass $reflection
     * @param bool             $recursively
     *
     * @return array
     */
    private function fetchMessages(\ReflectionClass $reflection, $recursively = false)
    {
        $target = $reflection->getDefaultProperties() + $reflection->getConstants();

        foreach ($reflection->getProperties() as $property) {
            if (strpos($property->getDocComment(), "@do-not-index")) {
                unset($target[$property->getName()]);
            }
        }

        $strings = [];
        array_walk_recursive($target, function ($value) use (&$strings) {
            if (is_string($value) && Translator::isMessage($value)) {
                $strings[] = $this->prepareMessage($value);
            }
        });

        if ($recursively && $reflection->getParentClass()) {
            //Joining strings data with parent class values (inheritance ON) - resolved into same
            //domain on export
            $strings = array_merge(
                $strings,
                $this->fetchMessages($reflection->getParentClass(), true)
            );
        }

        return $strings;
    }

    /**
     * Get associated domain.
     *
     * @param ReflectionInvocation $invocation
     *
     * @return string
     */
    private function invocationDomain(ReflectionInvocation $invocation): string
    {
        //Translation using default bundle
        $domain = $this->config->defaultDomain();

        if ($invocation->getName() == 'say') {
            //Let's try to confirm domain
            $domain = $this->config->resolveDomain($invocation->getClass());
        }

        //`l` and `p`, `say` functions
        $argument = null;
        switch (strtolower($invocation->getName())) {
            case 'say':
            case 'l':
                if ($invocation->countArguments() >= 3) {
                    $argument = $invocation->getArgument(2);
                }
                break;
            case 'p':
                if ($invocation->countArguments() >= 4) {
                    $argument = $invocation->getArgument(3);
                }
        }

        if (!empty($argument) && $argument->getType() == ReflectionArgument::STRING) {
            //Domain specified in arguments
            $domain = $this->config->resolveDomain($argument->stringValue());
        }

        return $domain;
    }

    /**
     * Remove [[ and ]] braces from translated string.
     *
     * @param string $string
     *
     * @return string
     */
    private function prepareMessage(string $string): string
    {
        if (Translator::isMessage($string)) {
            $string = substr($string, 2, -2);
        }

        return $string;
    }
}