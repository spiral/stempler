<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge\Inject;

use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\BlockClaims;
use Spiral\Stempler\Transform\BlockFetcher;
use Spiral\Stempler\Transform\QuotedValue;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Injects block values into PHP source code using marco function.
 */
final class InjectPHP implements VisitorInterface
{
    // php marcos to inject values into
    private const PHP_MACRO_FUNCTION = 'value';

    /** @var BlockClaims */
    private $blocks;

    /** @var BlockFetcher */
    private $fetcher;

    /**
     * @param BlockClaims $blocks
     */
    public function __construct(BlockClaims $blocks)
    {
        $this->blocks = $blocks;
        $this->fetcher = new BlockFetcher();
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx)
    {
        if (!$node instanceof PHP || strpos($node->content, self::PHP_MACRO_FUNCTION) === false) {
            return null;
        }

        $php = new PHPMixin($node->tokens, self::PHP_MACRO_FUNCTION);

        foreach ($this->blocks->getNames() as $name) {
            if ($php->has($name)) {
                $block = $this->trimPHP($this->blocks->claim($name));
                if ($block === '') {
                    $block = 'null';
                }

                $php->set($name, $block);
            }
        }

        $node->content = $php->compile();
        $node->tokens = token_get_all($node->content);
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {
    }

    /**
     * @param array|NodeInterface $node
     * @return string
     */
    private function trimPHP($node): string
    {
        switch (true) {
            case is_array($node):
                $result = [];
                foreach ($node as $child) {
                    $result[] = $this->trimPHP($child);
                }

                return join('.', $result);

            case $node instanceof Mixin:
                $result = [];
                foreach ($node->nodes as $child) {
                    $result[] = $this->trimPHP($child);
                }

                return join('.', $result);

            case $node instanceof Raw:
                return $this->exportValue($node);

            case $node instanceof Output:
                return trim($node->body);

            case $node instanceof PHP:
                return (new PHPMixin($node->tokens, self::PHP_MACRO_FUNCTION))->trimBody();

            case $node instanceof QuotedValue:
                return $this->trimPHP($node->trimValue());
        }

        return '';
    }

    /**
     * @param Raw $node
     * @return string
     */
    private function exportValue(Raw $node): string
    {
        $value = $node->content;
        switch (true) {
            case strtolower($value) === 'true':
                return 'true';
            case strtolower($value) === 'false':
                return 'false';
            case is_float($value) || is_numeric($value):
                return (string)$value;
        }

        return var_export($node->content, true);
    }
}
