<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

/**
 * Automatically invokes methods associated with directive name.
 */
abstract class AbstractDirective implements DirectiveInterface
{
    /** @var \ReflectionObject */
    private $r;

    /**
     * AbstractDirective constructor.
     */
    public function __construct()
    {
        $this->r = new \ReflectionObject($this);
    }

    /**
     * @param Directive $directive
     * @return string|null
     */
    public function render(Directive $directive): ?string
    {
        if (!$this->r->hasMethod('render' . ucfirst($directive->name))) {
            return null;
        }

        return call_user_func([$this, 'render' . ucfirst($directive->name)], $directive);
    }
}