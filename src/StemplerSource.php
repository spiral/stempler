<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

/**
 * Default implementation for ContextInterface.
 */
final class StemplerSource
{
    /**
     * Must be local stream.
     *
     * @var string
     */
    private $filename;

    /**
     * @var null|string
     */
    private $source = null;

    /**
     * @param string $filename
     * @param string $code
     */
    public function __construct(string $filename, string $code = null)
    {
        $this->filename = $filename;
        $this->source = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->source ?? file_get_contents($this->filename);
    }
}