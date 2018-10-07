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

    // Must be implemented
    public const TYPE_DIRECTIVE = 'directive';

    /**
     * In strict mode every unpaired close tag or other html error will raise an
     * StrictModeException.
     *
     * @return bool
     */
    public function isStrict(): bool;

    /**
     * Regular expression which defined short node tag, must declare named pattern with "name" and
     * "default" keys
     *
     * @return string
     */
    public function shortTags(): string;

    /**
     * Detect token behaviour.
     *
     * @param array  $token
     * @param string $name Node name stripper from token name.
     *
     * @return string
     */
    public function tokenType(array $token, &$name = null): string;

    /**
     * Resolve include or extend location based on given token.
     *
     * @param array $token
     *
     * @return string
     * @throws SyntaxException
     */
    public function resolvePath(array $token): string;

    /**
     * @param array      $token
     * @param Supervisor $supervisor
     *
     * @return ImporterInterface
     *
     * @throws SyntaxException
     */
    public function createImporter(array $token, Supervisor $supervisor): ImporterInterface;

    /**
     * Get all syntax block exporters.
     *
     * @return ExporterInterface[]
     */
    public function blockExporters(): array;
}