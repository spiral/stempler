<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Views\ViewSource;

class Cache
{
    /** @var string|null */
    private $directory;

    /** @var FilesInterface */
    private $files;

    /**
     * @param string|null    $directory Set to null to disable view cache.
     * @param FilesInterface $files
     */
    public function __construct(string $directory = null, FilesInterface $files = null)
    {
        $this->directory = $directory;
        $this->files = $files ?? new Files();
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(ViewSource $source, string $className)
    {
        if (empty($this->directory)) {
            // pass all template rendering thought filesystem to ensure proper line linking
            return tempnam(sys_get_temp_dir(), 'stempler') . '.php';
        }

        $prefix = sprintf("%s:%s:%s", $source->getNamespace(), $source->getName(), $className);
        $prefix = preg_replace('/([^A-Za-z0-9]|\-)+/', '-', $prefix);

        return sprintf("%s/%s.php", rtrim($this->directory, '/') . '/', $prefix);
    }

    /**
     * Delete cached files.
     *
     * @param ViewSource $source
     * @param string     $className
     */
    public function delete(ViewSource $source, string $className)
    {
        try {
            $this->files->delete($this->getKey($source, $className));
        } catch (\Throwable $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $key, string $content)
    {
        $this->files->write($key, $content, FilesInterface::RUNTIME, true);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $key)
    {
        if ($this->files->exists($key)) {
            include_once $key;
        }
    }
}