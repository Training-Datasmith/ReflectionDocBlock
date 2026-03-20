<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */
namespace Php_Documentor\Reflection\Doc_Block\Tags;

use function array_key_exists;
use Php_Documentor\Reflection\Doc_Block\Tag;
use function preg_match;
use function rawurlencode;
use function str_replace;
use function strpos;
use function trim;
use Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}example tag in a Docblock.
 */
final class Example implements Tag
{
    /** @var string Path to a file to use as an example. May also be an absolute URI. */
    private string $file_path;
    /**
     * @var bool Whether the file path component represents an URI. This determines how the file portion
     *     appears at {@link getContent()}.
     */
    private bool $is_uri;
    private int $starting_line;
    private int $line_count;
    private ?string $content = null;
    public function __construct(string $file_path, bool $is_uri, int $starting_line, int $line_count, ?string $content)
    {
        Assert::string_not_empty($file_path);
        Assert::greater_than_eq($starting_line, 1);
        Assert::greater_than_eq($line_count, 0);
        $this->file_path = $file_path;
        $this->starting_line = $starting_line;
        $this->line_count = $line_count;
        if ($content !== null) {
            $this->content = trim($content);
        }
        $this->is_uri = $is_uri;
    }
    public function get_content(): string
    {
        if ($this->content === null || $this->content === '') {
            $file_path = $this->file_path;
            if ($this->is_uri) {
                $file_path = $this->is_uri_relative($this->file_path) ? str_replace('%2F', '/', rawurlencode($this->file_path)) : $this->file_path;
            }
            return trim($file_path);
        }
        return $this->content;
    }
    public function get_description(): ?string
    {
        return $this->content;
    }
    public static function create(string $body): ?Tag
    {
        // File component: File path in quotes or File URI / Source information
        if (!preg_match('/^\s*(?:(\"[^\"]+\")|(\S+))(?:\s+(.*))?$/sux', $body, $matches)) {
            return null;
        }
        $file_path = null;
        $file_uri = null;
        if (array_key_exists(1, $matches) && $matches[1] !== '') {
            $file_path = $matches[1];
        } else {
            $file_uri = array_key_exists(2, $matches) ? $matches[2] : '';
        }
        $starting_line = 1;
        $line_count = 0;
        $description = null;
        if (array_key_exists(3, $matches)) {
            $description = $matches[3];
            // Starting line / Number of lines / Description
            if (preg_match('/^([1-9]\d*)(?:\s+((?1))\s*)?(.*)$/sux', $matches[3], $content_matches)) {
                $starting_line = (int) $content_matches[1];
                if (isset($content_matches[2])) {
                    $line_count = (int) $content_matches[2];
                }
                if (array_key_exists(3, $content_matches)) {
                    $description = $content_matches[3];
                }
            }
        }
        return new static($file_path ?? $file_uri ?? '', $file_uri !== null, $starting_line, $line_count, $description);
    }
    /**
     * Returns the file path.
     *
     * @return string Path to a file to use as an example.
     *     May also be an absolute URI.
     */
    public function get_file_path(): string
    {
        return trim($this->file_path, '"');
    }
    /**
     * Returns a string representation for this tag.
     */
    public function __toString(): string
    {
        $file_path = $this->file_path;
        $is_default_line = $this->starting_line === 1 && $this->line_count === 0;
        $starting_line = !$is_default_line ? (string) $this->starting_line : '';
        $line_count = !$is_default_line ? (string) $this->line_count : '';
        $content = (string) $this->content;
        return $file_path . ($starting_line !== '' ? ($file_path !== '' ? ' ' : '') . $starting_line : '') . ($line_count !== '' ? ($file_path !== '' || $starting_line !== '' ? ' ' : '') . $line_count : '') . ($content !== '' ? ($file_path !== '' || $starting_line !== '' || $line_count !== '' ? ' ' : '') . $content : '');
    }
    /**
     * Returns true if the provided URI is relative or contains a complete scheme (and thus is absolute).
     */
    private function is_uri_relative(string $uri): bool
    {
        return strpos($uri, ':') === false;
    }
    public function get_starting_line(): int
    {
        return $this->starting_line;
    }
    public function get_line_count(): int
    {
        return $this->line_count;
    }
    public function get_name(): string
    {
        return 'example';
    }
    public function render(?Formatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new Formatter\Passthrough_Formatter();
        }
        return $formatter->format($this);
    }
}