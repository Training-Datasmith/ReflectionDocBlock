<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */
namespace Php_Documentor\Reflection\Doc_Block;

use Php_Documentor\Reflection\Doc_Block;
use Php_Documentor\Reflection\Doc_Block\Tags\Formatter;
use Php_Documentor\Reflection\Doc_Block\Tags\Formatter\Passthrough_Formatter;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strlen;
use function wordwrap;
/**
 * Converts a DocBlock back from an object to a complete DocComment including Asterisks.
 */
class Serializer
{
    /** @var string The string to indent the comment with. */
    protected string $indent_string = ' ';
    /** @var int The number of times the indent string is repeated. */
    protected int $indent = 0;
    /** @var bool Whether to indent the first line with the given indent amount and string. */
    protected bool $is_first_line_indented = true;
    /** @var int|null The max length of a line. */
    protected ?int $line_length = null;
    /** @var Formatter A custom tag formatter. */
    protected Formatter $tag_formatter;
    private string $line_ending;
    /**
     * Create a Serializer instance.
     *
     * @param int       $indent          The number of times the indent string is repeated.
     * @param string    $indentString    The string to indent the comment with.
     * @param bool      $indentFirstLine Whether to indent the first line.
     * @param int|null  $lineLength      The max length of a line or NULL to disable line wrapping.
     * @param Formatter $tagFormatter    A custom tag formatter, defaults to PassthroughFormatter.
     * @param string    $lineEnding      Line ending used in the output, by default \n is used.
     */
    public function __construct(int $indent = 0, string $indent_string = ' ', bool $indent_first_line = true, ?int $line_length = null, ?Formatter $tag_formatter = null, string $line_ending = "\n")
    {
        $this->indent = $indent;
        $this->indent_string = $indent_string;
        $this->is_first_line_indented = $indent_first_line;
        $this->line_length = $line_length;
        $this->tag_formatter = $tag_formatter ?: new Passthrough_Formatter();
        $this->line_ending = $line_ending;
    }
    /**
     * Generate a DocBlock comment.
     *
     * @param DocBlock $docblock The DocBlock to serialize.
     *
     * @return string The serialized doc block.
     */
    public function get_doc_comment(Doc_Block $docblock): string
    {
        $indent = str_repeat($this->indent_string, $this->indent);
        $first_indent = $this->is_first_line_indented ? $indent : '';
        // 3 === strlen(' * ')
        $wrap_length = $this->line_length !== null ? $this->line_length - strlen($indent) - 3 : null;
        $text = $this->remove_trailing_spaces($indent, $this->add_asterisks_for_each_line($indent, $this->get_summary_and_description_text_block($docblock, $wrap_length)));
        $comment = $first_indent . "/**\n";
        if ($text) {
            $comment .= $indent . ' * ' . $text . "\n";
            $comment .= $indent . " *\n";
        }
        $comment = $this->add_tag_block($docblock, $wrap_length, $indent, $comment);
        return str_replace("\n", $this->line_ending, $comment . $indent . ' */');
    }
    private function remove_trailing_spaces(string $indent, string $text): string
    {
        return str_replace(sprintf("\n%s * \n", $indent), sprintf("\n%s *\n", $indent), $text);
    }
    private function add_asterisks_for_each_line(string $indent, string $text): string
    {
        return str_replace("\n", sprintf("\n%s * ", $indent), $text);
    }
    private function get_summary_and_description_text_block(Doc_Block $docblock, ?int $wrap_length): string
    {
        $text = $docblock->get_summary() . ((string) $docblock->get_description() ? "\n\n" . $docblock->get_description() : '');
        if ($wrap_length !== null) {
            return wordwrap($text, $wrap_length);
        }
        return $text;
    }
    private function add_tag_block(Doc_Block $docblock, ?int $wrap_length, string $indent, string $comment): string
    {
        foreach ($docblock->get_tags() as $tag) {
            $tag_text = $this->tag_formatter->format($tag);
            if ($wrap_length !== null) {
                $tag_text = wordwrap($tag_text, $wrap_length);
            }
            $tag_text = str_replace("\n", sprintf("\n%s * ", $indent), $tag_text);
            $comment .= sprintf("%s * %s\n", $indent, $tag_text);
        }
        return $comment;
    }
}