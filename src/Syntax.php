<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Import;

/**
 * Default Stempler syntax - Woo. Provides ability to define blocks, extends and includes.
 */
final class Syntax implements SyntaxInterface
{
    private const PATH_ATTRIBUTE = 'path';
    private const SHORT_TAGS     = '/\${(?P<name>[a-z0-9_\.\-]+)(?: *\| *(?P<default>[^}]+) *)?}/i';
    private const SYNTAX         = [
        self::TYPE_BLOCK     => ['block:', 'section:', 'yield:', 'define:'],
        self::TYPE_EXTENDS   => ['extends:', 'extends', 'dark:extends', 'layout:extends'],
        self::PATH_ATTRIBUTE => ['path', 'layout', 'dark:path', 'dark:layout'],
        self::TYPE_IMPORTER  => ['dark:use', 'use', 'node:use', 'stempler:use']
    ];

    /** @var bool */
    private $strict = true;

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
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * {@inheritdoc}
     */
    public function parseBlock(string $content): ?array
    {
        if (preg_match(self::SHORT_TAGS, $content, $matches)) {
            return $matches;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tokenType(array $token, &$name = null): string
    {
        $name = $token[HtmlTokenizer::TOKEN_NAME];
        foreach (self::SYNTAX as $type => $prefixes) {
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
    public function fetchPath(array $token): string
    {
        // Needed to fetch token name
        $this->tokenType($token, $name);

        foreach (self::SYNTAX[self::PATH_ATTRIBUTE] as $attribute) {
            if (isset($token[HtmlTokenizer::TOKEN_ATTRIBUTES][$attribute])) {
                return $token[HtmlTokenizer::TOKEN_ATTRIBUTES][$attribute];
            }
        }

        // By default we can count token name as needed path
        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function createImport(array $token, CompilerInterface $compiler): ImportInterface
    {
        //Fetching path
        $path = $this->fetchPath($token);
        if (empty($attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES])) {
            throw new SyntaxException("Invalid import element syntax, attributes missing", $token);
        }

        /**
         * <dark:use bundle="path-to-bundle"/>
         */
        if (isset($attributes['bundle'])) {
            $path = $attributes['bundle'];

            return new Import\Bundle($compiler->createNode($path, $token));
        }

        /**
         * <dark:use path="path-to-element" as="tag"/>
         * <dark:use path="path-to-element" element="tag"/>
         */
        if (isset($attributes['element']) || isset($attributes['as'])) {
            $alias = isset($attributes['element']) ? $attributes['element'] : $attributes['as'];

            return new Import\Alias($alias, $path);
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

            return new Import\Prefix($prefix, $path);
        }

        if (isset($attributes['stop'])) {
            return new Import\Stop($attributes['stop']);
        }

        throw new SyntaxException("Undefined use element", $token);
    }
}