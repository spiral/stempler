<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Import;

use Spiral\Stempler\ImportInterface;
use Spiral\Stempler\Node;

/**
 * Create import bundle based on imported template content.
 */
class Bundle implements ImportInterface
{
    /**
     * @var ImportInterface[]
     */
    protected $imports = [];

    /**
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->imports = $node->getCompiler()->getImports();
    }

    /**
     * {@inheritdoc}
     */
    public function importable(string $element, array $token): bool
    {
        foreach ($this->imports as $importer) {
            if ($importer->importable($element, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath(string $element, array $token): ?string
    {
        foreach ($this->imports as $importer) {
            if ($importer->importable($element, $token)) {
                return $importer->resolvePath($element, $token);
            }
        }

        return null;
    }
}