<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Compiler;

use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\Source;

/**
 * Stores and resolves offsets and line numbers between templates.
 */
final class SourceMap implements \Serializable
{
    /** @var array */
    private $paths = [];

    /** @var array */
    private $grammar = [];

    /** @var array */
    private $lines = [];

    /** @var Source[] */
    private $sourceCache = null;

    /**
     * Calculate the location of all closest nodes based on a line number in generated source. Recursive until top root
     * template.
     *
     * @param int $line
     * @return array
     */
    public function getStack(int $line): array
    {
        $found = null;
        foreach ($this->lines as $linen => $ctx) {
            if ($linen <= $line) {
                $found = $ctx;
            }
        }

        if ($found === null) {
            return [];
        }

        $result = [];
        $this->unpack($result, $found);

        return $result;
    }

    /**
     * Compress.
     *
     * @return false|string
     */
    public function serialize()
    {
        return json_encode([
            'paths'     => $this->paths,
            'grammar'   => $this->grammar,
            'locations' => $this->lines
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $this->paths = $data['paths'];
        $this->grammar = $data['grammar'];
        $this->lines = $data['locations'];
    }

    /**
     * @param array $result
     * @param array $line
     */
    private function unpack(array &$result, array $line)
    {
        $result[] = [
            'file'    => $this->paths[$line[1]],
            'line'    => $line[2],
            'grammar' => $this->grammar[$line[0]],
        ];

        if ($line[3] !== null) {
            $this->unpack($result, $line[3]);
        }
    }

    /**
     * @param string          $content
     * @param array           $locations
     * @param LoaderInterface $loader
     * @return SourceMap
     */
    public static function calculate(string $content, array $locations, LoaderInterface $loader): SourceMap
    {
        $map = new self;

        foreach ($locations as $offset => $location) {
            $line = Source::resolveLine($content, $offset);
            if (isset($map->lines[$line])) {
                // PHP can only identify the line in case of the exception, overlap on next line
                $line++;
            }

            $map->lines[$line] = $map->calculateLine($location, $loader);
        }

        $map->sourceCache = null;

        return $map;
    }

    /**
     * @param Location        $location
     * @param LoaderInterface $loader
     * @return array
     */
    private function calculateLine(Location $location, LoaderInterface $loader): array
    {
        if (!isset($this->sourceCache[$location->path])) {
            $this->sourceCache[$location->path] = $loader->load($location->path);
        }

        $path = $this->sourceCache[$location->path]->getFilename() ?? $location->path;

        if (!in_array($location->grammar, $this->grammar, true)) {
            $this->grammar[] = $location->grammar;
        }

        if (!in_array($path, $this->paths, true)) {
            $this->paths[] = $path;
        }

        return [
            array_search($location->grammar, $this->grammar),
            array_search($path, $this->paths),
            Source::resolveLine($this->sourceCache[$location->path]->getContent(), $location->offset),
            $location->parent === null ? null : $this->calculateLine($location->parent, $loader)
        ];
    }
}