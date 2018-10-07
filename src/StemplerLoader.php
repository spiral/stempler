<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Stempler;

use Spiral\Files\FileManager;
use Spiral\Files\FilesInterface;
use Spiral\Stempler\Exceptions\LoaderException;

/**
 * Simple loader with ability to use multiple namespaces. Copy from TwigLoader.
 */
class StemplerLoader implements LoaderInterface
{
    const DEFAULT_NAMESPACE = 'default';
    const FILE_EXTENSION    = 'php';

    /**
     * Path chunks.
     */
    const VIEW_FILENAME  = 0;
    const VIEW_NAMESPACE = 1;
    const VIEW_NAME      = 2;

    /**
     * Available view namespaces associated with their directories.
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param array          $namespaces
     * @param FilesInterface $files
     */
    public function __construct(array $namespaces, FilesInterface $files = null)
    {
        $this->namespaces = $namespaces;
        $this->files = $files ?? new FileManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(string $path): StemplerSource
    {
        return new StemplerSource(
            $this->locateView($path)[self::VIEW_FILENAME]
        );
    }

    /**
     * Locate view filename based on current loader settings.
     *
     * @param string $path
     *
     * @return array [namespace, name]
     *
     * @throws LoaderException
     */
    protected function locateView(string $path): array
    {
        //Making sure requested name is valid
        $this->validatePath($path);

        list($namespace, $filename) = $this->parsePath($path);

        foreach ($this->namespaces[$namespace] as $directory) {
            //Seeking for view filename
            if ($this->files->exists($directory . $filename)) {
                return [
                    self::VIEW_FILENAME  => $directory . $filename,
                    self::VIEW_NAMESPACE => $namespace,
                    self::VIEW_NAME      => $this->fetchName($filename)
                ];
            }
        }

        throw new LoaderException("Unable to locate view '{$filename}' in namespace '{$namespace}'");
    }

    /**
     * Fetch namespace and filename from view name or force default values.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws LoaderException
     */
    protected function parsePath(string $path): array
    {
        //Cutting extra symbols (see Twig)
        $filename = preg_replace('#/{2,}#', '/', str_replace('\\', '/', (string)$path));

        if (strpos($filename, '.') === false) {
            //Forcing default extension
            $filename .= '.' . static::FILE_EXTENSION;
        }

        if (strpos($filename, ':') !== false) {
            return explode(':', $filename);
        }

        //Let's force default namespace
        return [static::DEFAULT_NAMESPACE, $filename];
    }

    /**
     * Make sure view filename is OK. Same as in twig.
     *
     * @param string $path
     *
     * @throws LoaderException
     */
    protected function validatePath(string $path)
    {
        if (false !== strpos($path, "\0")) {
            throw new LoaderException('A template name cannot contain NUL bytes');
        }

        $path = ltrim($path, '/');
        $parts = explode('/', $path);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new LoaderException(sprintf(
                    'Looks like you try to load a template outside configured directories (%s)',
                    $path
                ));
            }
        }
    }

    /**
     * Resolve view name based on filename (depends on current extension settings).
     *
     * @param string $filename
     *
     * @return string
     */
    protected function fetchName(string $filename): string
    {
        return substr($filename, 0, -1 * (1 + strlen(static::FILE_EXTENSION)));
    }
}