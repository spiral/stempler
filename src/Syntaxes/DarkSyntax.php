<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Syntaxes;

use Spiral\Stempler\Exceptions\SyntaxException;
use Spiral\Stempler\Exporters\AttributesExporter;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Stempler\ImporterInterface;
use Spiral\Stempler\Importers\Aliaser;
use Spiral\Stempler\Importers\Bundler;
use Spiral\Stempler\Importers\Prefixer;
use Spiral\Stempler\Importers\Stopper;
use Spiral\Stempler\Supervisor;
use Spiral\Stempler\SyntaxInterface;

/**
 * Default Stempler syntax - Woo. Provides ability to define blocks, extends and includes.
 */
class DarkSyntax implements SyntaxInterface
{
    /**
     * Path attribute in extends and other nodes.
     */
    const PATH_ATTRIBUTE = 'path';

    /**
     * Short tags expression, usually used inside attributes and etc.
     */
    const SHORT_TAGS = '/\${(?P<name>[a-z0-9_\.\-]+)(?: *\| *(?P<default>[^}]+) *)?}/i';

    /**
     * @var bool
     */
    private $strict = true;

    /**
     * Stempler syntax options, syntax and names. Every option is required.
     *
     * @todo Something with DTD? Seems compatible.
     * @var array
     */
    protected $constructions = [
        self::TYPE_BLOCK     => ['block:', 'section:', 'yield:', 'define:'],
        self::TYPE_EXTENDS   => [
            'extends:',
            'extends',
            'dark:extends',
            'layout:extends'
        ],
        self::PATH_ATTRIBUTE => [
            'path',
            'layout',
            'dark:path',
            'dark:layout'
        ],
        self::TYPE_IMPORTER  => ['dark:use', 'use', 'node:use', 'stempler:use']
    ];

    /**
     * @param bool $strict
     */
    public function __construct(bool $strict = true)
    {
        $this->strict = $strict;
    }

    /**
     * {@inheritdoc}
     */
    public function tokenType(array $token, &$name = null): string
    {
        $name = $token[HtmlTokenizer::TOKEN_NAME];
        foreach ($this->constructions as $type => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (strpos($name, $prefix) === 0) {
                    //We found prefix pointing to needed behaviour
                    $name = substr($name, strlen($prefix));

                    return $type;
                }
            }
        }

        return self::TYPE_NONE;
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath(array $token): string
    {
        //Needed to fetch token name
        $this->tokenType($token, $name);

        foreach ($this->constructions[self::PATH_ATTRIBUTE] as $attribute) {
            if (isset($token[HtmlTokenizer::TOKEN_ATTRIBUTES][$attribute])) {
                return $token[HtmlTokenizer::TOKEN_ATTRIBUTES][$attribute];
            }
        }

        //By default we can count token name as needed path
        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * {@inheritdoc}
     */
    public function shortTags(): string
    {
        return self::SHORT_TAGS;
    }

    /**
     * {@inheritdoc}
     */
    public function createImporter(array $token, Supervisor $supervisor): ImporterInterface
    {
        //Fetching path
        $path = $this->resolvePath($token);
        if (empty($attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES])) {
            throw new SyntaxException("Invalid import element syntax, attributes missing", $token);
        }

        /**
         * <dark:use bundle="path-to-bundle"/>
         */
        if (isset($attributes['bundle'])) {
            $path = $attributes['bundle'];

            return new Bundler($supervisor, $path, $token);
        }

        /**
         * <dark:use path="path-to-element" as="tag"/>
         * <dark:use path="path-to-element" element="tag"/>
         */
        if (isset($attributes['element']) || isset($attributes['as'])) {
            $alias = isset($attributes['element']) ? $attributes['element'] : $attributes['as'];

            return new Aliaser($alias, $path);
        }

        //Now we have to decide what importer to use
        if (isset($attributes['namespace']) || isset($attributes['prefix'])) {
            if (strpos($path, '*') === false) {
                throw new SyntaxException(
                    "Path in namespace/prefix import must include start symbol", $token
                );
            }

            $prefix = isset($attributes['namespace'])
                ? $attributes['namespace'] . ':'
                : $attributes['prefix'];

            return new Prefixer($prefix, $path);
        }

        if (isset($attributes['stop'])) {
            return new Stopper($attributes['stop']);
        }

        throw new SyntaxException("Undefined use element", $token);
    }

    /**
     * {@inheritdoc}
     */
    public function blockExporters(): array
    {
        return [
            new AttributesExporter()
        ];
    }
}