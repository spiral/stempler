<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\AbstractEngine;
use Spiral\Views\ViewInterface;

class StemplerEngine extends AbstractEngine
{
    private $syntax;

    private $cache;

    public function __construct(SyntaxInterface $syntax, StemplerCache $cache = null)
    {
        $this->syntax = $syntax;
        $this->cache = $cache;
    }

    public function compile(string $path, ContextInterface $context)
    {
        return null;
    }

    public function reset(string $path, ContextInterface $context)
    {
        return null;
    }

    public function get(string $path, ContextInterface $context): ViewInterface
    {
        return null;
    }
}