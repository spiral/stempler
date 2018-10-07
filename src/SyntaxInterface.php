<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\SyntaxException;

/**
 * Used to detect token behaviour based on internal rules.
 */
interface SyntaxInterface
{
    // Block behaviours
    public const TYPE_BLOCK    = 'block';
    public const TYPE_EXTENDS  = 'extends';
    public const TYPE_IMPORTER = 'use';
    public const TYPE_INCLUDE  = 'include';
    public const TYPE_NONE     = 'none';

    /**
     * In strict mode every unpaired close tag or other html error will raise an
     * StrictModeException.
     *
     * @return bool
     */
    public function isStrict(): bool;

    /**
     * Parse string to identify location of short block tag. Syntax specific.
     *
     * @param string $content
     * @return array
     */
    public function parseBlock(string $content): ?array;

    /**
     * Detect token behaviour.
     *
     * @param array  $token
     * @param string $name Node name stripper from token name.
     * @return string
     */
    public function tokenType(array $token, &$name = null): string;

    /**
     * Resolve include or extend location based on given token.
     *
     * @param array $token
     * @return string
     *
     * @throws SyntaxException
     */
    public function fetchPath(array $token): string;

    /**
     * @param array             $token
     * @param CompilerInterface $compiler
     * @return ImportInterface
     *
     * @throws SyntaxException
     */
    public function createImport(array $token, CompilerInterface $compiler): ImportInterface;


}