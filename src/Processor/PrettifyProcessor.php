<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Processor;

use Spiral\Stempler\HtmlTokenizer;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\ContextInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

class PrettifyProcessor implements ProcessorInterface
{
    /*** @var HtmlTokenizer */
    private $tokenizer = null;

    /** @var array */
    private $options = [
        //Drop blank lines
        'endings'    => true,

        //Trim attributes
        'attributes' => [
            'normalize' => true,

            //Drop spaces
            'trim'      => ['class', 'style', 'id'],

            //Drop when empty
            'drop'      => ['class', 'style', 'id']
        ]
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->tokenizer = new HtmlTokenizer();
        $this->options = $options + $this->options;
    }

    /**
     * @inheritdoc
     */
    public function process(ViewSource $source, ContextInterface $context): ViewSource
    {
        $code = $source->getCode();

        if ($this->options['endings']) {
            $code = $this->normalizeEndings($source->getCode(), new Isolator());
        }

        if ($this->options['attributes']['normalize']) {
            $code = $this->normalizeAttributes($code, $this->tokenizer);
        }

        return $source->withCode($code);
    }

    /**
     * Remove blank lines.
     *
     * @param string   $source
     * @param Isolator $isolator
     *
     * @return string
     */
    private function normalizeEndings($source, Isolator $isolator)
    {
        //Step #1, \n only
        $source = $isolator->isolatePHP(preg_replace('/[\n\r]+/', "\n", $source));

        //Step #2, chunk by lines
        $sourceLines = explode("\n", $source);

        //Step #3, no blank lines and html comments (will keep conditional commends)
        $sourceLines = array_filter($sourceLines, function ($line) {
            return trim($line);
        });

        $source = $isolator->repairPHP(join("\n", $sourceLines));
        $isolator->reset();

        return $source;
    }

    /**
     * Normalize attribute values.
     *
     * @param string        $source
     * @param HtmlTokenizer $tokenizer
     *
     * @return mixed
     */
    private function normalizeAttributes($source, HtmlTokenizer $tokenizer)
    {
        $result = '';
        foreach ($tokenizer->parse($source) as $token) {
            if (empty($token[HtmlTokenizer::TOKEN_ATTRIBUTES])) {
                $result .= $tokenizer->compileToken($token);
                continue;
            }

            $attributes = [];
            foreach ($token[HtmlTokenizer::TOKEN_ATTRIBUTES] as $attribute => $value) {
                if (in_array($attribute, $this->options['attributes']['trim'])) {
                    $value = trim($value);
                }

                if (empty($value) && in_array($attribute, $this->options['attributes']['drop'])) {
                    //Empty value
                    continue;
                }

                $attributes[$attribute] = $value;
            }

            $token[HtmlTokenizer::TOKEN_ATTRIBUTES] = $attributes;
            $result .= $tokenizer->compileToken($token);
        }

        return $result;
    }
}