<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Export;

use Spiral\Stempler\Export\Traits\FilterTrait;
use Spiral\Stempler\ExportInterface;

/**
 * Export user defined (outer) blocks as tag attributes.
 *
 * Use following pattern: node:attributes[="condition"]
 */
class AttributeExport implements ExportInterface
{
    use FilterTrait;

    /**
     * {@inheritdoc}
     */
    public function mountBlocks(string $content, array $blocks): string
    {
        if (preg_match_all('/ node:attributes(?:=\"([^\'"]+)\")?/i', $content, $matches)) {
            //We have to sort from longest to shortest
            uasort($matches[0], function ($replaceA, $replaceB) {
                return strlen($replaceB) - strlen($replaceA);
            });

            foreach ($matches[0] as $id => $replace) {
                $inject = [];

                //That's why we need longest first (prefix mode)
                foreach ($this->filterBlocks($matches[1][$id], $blocks) as $name => $value) {
                    if ($value === null) {
                        $inject[$name] = $name;
                        continue;
                    }

                    $inject[$name] = $name . '="' . $value . '"';
                }

                //Injecting
                $content = str_replace(
                    $replace,
                    $inject ? ' ' . join(' ', $inject) : '',
                    $content
                );
            }
        }

        return $content;
    }
}