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
 * Compiles one or multiple directives.
 */
interface DirectiveInterface
{
    /**
     * @param Directive $directive
     * @return string|null
     */
    public function render(Directive $directive): ?string;
}