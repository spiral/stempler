<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Stempler;
use Spiral\Stempler\StemplerLoader;
use Spiral\Stempler\Syntax\DarkSyntax;

abstract class BaseTest extends TestCase
{
    /**
     * Render view and return it's blank lines.
     *
     * @param string $view
     *
     * @return array
     */
    protected function compile($view)
    {
        $stempler = new Stempler(
            new StemplerLoader([
                'default'   => [__DIR__ . '/fixtures/default/'],
                'namespace' => [__DIR__ . '/fixtures/namespace/',]
            ]),
            new DarkSyntax()
        );

        $lines = explode("\n", self::normalizeEndings($stempler->compile($view)));

        return array_values(array_map('trim', array_filter($lines, 'trim')));
    }

    /**
     * Render view and return it's blank lines.
     *
     * @param string $string
     *
     * @return array
     */
    protected function compileString($string)
    {
        $stempler = new Stempler(
            new StemplerLoader([
                'default'   => [__DIR__ . '/fixtures/default/'],
                'namespace' => [__DIR__ . '/fixtures/namespace/',]
            ]),
            new DarkSyntax()
        );

        $lines = explode("\n", self::normalizeEndings($stempler->compileString($string)));

        return array_values(array_map('trim', array_filter($lines, 'trim')));
    }


    /**
     * Normalize string endings to avoid EOL problem. Replace \n\r and multiply new lines with
     * single \n.
     *
     * @param string $string       String to be normalized.
     * @param bool   $joinMultiple Join multiple new lines into one.
     *
     * @return string
     */
    public static function normalizeEndings(string $string, bool $joinMultiple = true): string
    {
        if (!$joinMultiple) {
            return str_replace("\r\n", "\n", $string);
        }

        return preg_replace('/[\n\r]+/', "\n", $string);
    }
}