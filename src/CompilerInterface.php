<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\CompileException;

/**
 * SupervisorInterface used by Node to define html syntax for control elements and create valid
 * behaviour for html constructions.
 *
 * @see BehaviourInterface
 * @see ExtendsBehaviourInterface
 * @see BlockBehaviourInterface
 * @see IncludeBehaviourInterface
 */
interface CompilerInterface
{
    /**
     * Get unique placeholder name, unique names are required in some cases to correctly process
     * includes and etc.
     *
     * @return string
     */
    public function generateID(): string;

    /**
     * @return SyntaxInterface
     */
    public function getSyntax(): SyntaxInterface;

    /**
     * Compile path.
     *
     * @param string $path
     * @return string
     *
     * @throws CompileException
     */
    public function compile(string $path): string;

    /**
     * Compile string.
     *
     * @param string $source
     * @return string
     *
     * @throws CompileException
     */
    public function compileString(string $source): string;

    /**
     * Create node based on given location with identical supervisor (cloned).
     *
     * @param string $path
     * @param array  $token Context token.
     * @return Node
     *
     * @throws CompileException
     */
    public function createNode(string $path, array $token = []): Node;

    /**
     * Get all namespace and path imports active within current compiler.
     *
     * @return ImportInterface[]
     */
    public function getImports(): array;

    /**
     * Helper classes used to export attributes from tokens and blocks.
     *
     * @return ExportInterface[]
     */
    public function getExports(): array;

    /**
     * Define html tag behaviour based on supervisor syntax settings.
     *
     * @param array $token
     * @param array $content
     * @param Node  $node Node which called behaviour creation. Just in case.
     * @return mixed|BehaviourInterface
     */
    public function defineToken(array $token, array $content, Node $node);
}