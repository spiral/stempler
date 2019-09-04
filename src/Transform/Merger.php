<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Transform;

use DeepCopy\DeepCopy;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Transform\Merge\Inject;
use Spiral\Stempler\Traverser;
use Spiral\Stempler\VisitorInterface;

/**
 * Merges two ASTs. Used by extend and import blocks. Called inside ResolveImports,
 * ExtendsParent visitors.
 */
final class Merger
{
    /** @var DeepCopy */
    private $deepCopy;

    /** @var BlockFetcher */
    private $fetcher;

    /**
     * Merger constructor.
     */
    public function __construct()
    {
        $this->deepCopy = new DeepCopy();
        $this->fetcher = new BlockFetcher();
    }

    /**
     * @return BlockFetcher
     */
    public function getFetcher(): BlockFetcher
    {
        return $this->fetcher;
    }

    /**
     * Merge given template with array of blocks.
     *
     * @param Template $target
     * @param Tag      $source
     * @return Template
     */
    public function merge(Template $target, Tag $source): Template
    {
        $blocks = $this->fetcher->fetchBlocks($source);

        // to avoid issues caused by shared nodes
        $target = $this->deepCopy->copy($target);
        $target->setContext($source->getContext());

        $target->nodes = $this->inject($target->nodes, new Inject\InjectBlocks($blocks));
        $target->nodes = $this->inject($target->nodes, new Inject\InjectPHP($blocks));
        $target->nodes = $this->inject($target->nodes, new Inject\InjectAttributes($blocks));

        return $target;
    }

    /**
     * @param array            $nodes
     * @param VisitorInterface $visitor
     * @return array|NodeInterface[]
     */
    protected function inject(array $nodes, VisitorInterface $visitor)
    {
        $traverser = new Traverser();
        $traverser->addVisitor($visitor);

        return $traverser->traverse($nodes);
    }
}