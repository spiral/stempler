<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Views\Exception\RenderException;
use Spiral\Views\ViewInterface;

abstract class StemplerView implements ViewInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(array $data = []): string
    {
        ob_start();
        $__outputLevel__ = ob_get_level();

        try {
            $this->execute($data);
        } catch (\Throwable $e) {
            while (ob_get_level() >= $__outputLevel__) {
                ob_end_clean();
            }

            throw new RenderException($e);
        } finally {
            //Closing all nested buffers
            while (ob_get_level() > $__outputLevel__) {
                ob_end_clean();
            }
        }

        return ob_get_clean();
    }

    /**
     * Execute template.
     *
     * @param array $data
     */
    abstract protected function execute(array $data);
}