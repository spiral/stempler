<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Exception;

use Spiral\Stempler\Exception\Traits\ContextTrait;

class ExtendsException extends \RuntimeException implements ContextExceptionInterface
{
    use ContextTrait;
}