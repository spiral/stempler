<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Lexer\Token;

/**
 * Defines the node location in a source code.
 */
final class Context
{
    /** @var Token */
    private $token;

    /** @var string|null */
    private $path;

    public $parent;

    /**
     * @param Token       $token
     * @param string|null $path
     */
    public function __construct(Token $token, string $path = null)
    {
        $this->token = $token;
        $this->path = $path;
    }

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
