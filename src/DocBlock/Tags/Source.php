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

use Php_Documentor\Reflection\Doc_Block\Description;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use function preg_match;
use Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}source tag in a Docblock.
 */
final class Source extends Base_Tag
{
    protected string $name = 'source';
    /** @var int The starting line, relative to the structural element's location. */
    private int $starting_line;
    /** @var int|null The number of lines, relative to the starting line. NULL means "to the end". */
    private ?int $line_count = null;
    /**
     * @param int|string      $startingLine should be a to int convertible value
     * @param int|string|null $lineCount    should be a to int convertible value
     */
    public function __construct($starting_line, $line_count = null, ?Description $description = null)
    {
        Assert::integerish($starting_line);
        Assert::null_or_integerish($line_count);
        $this->starting_line = (int) $starting_line;
        $this->line_count = $line_count !== null ? (int) $line_count : null;
        $this->description = $description;
    }
    public static function create(string $body, ?Description_Factory $description_factory = null, ?Type_Context $context = null): self
    {
        Assert::string_not_empty($body);
        Assert::not_null($description_factory);
        $starting_line = 1;
        $line_count = null;
        $description = null;
        // Starting line / Number of lines / Description
        if (preg_match('/^([1-9]\d*)\s*(?:((?1))\s+)?(.*)$/sux', $body, $matches)) {
            $starting_line = (int) $matches[1];
            if (isset($matches[2]) && $matches[2] !== '') {
                $line_count = (int) $matches[2];
            }
            $description = $matches[3];
        }
        return new static($starting_line, $line_count, $description_factory->create($description ?? '', $context));
    }
    /**
     * Gets the starting line.
     *
     * @return int The starting line, relative to the structural element's
     *     location.
     */
    public function get_starting_line(): int
    {
        return $this->starting_line;
    }
    /**
     * Returns the number of lines.
     *
     * @return int|null The number of lines, relative to the starting line. NULL
     *     means "to the end".
     */
    public function get_line_count(): ?int
    {
        return $this->line_count;
    }
    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $starting_line = (string) $this->starting_line;
        $line_count = $this->line_count !== null ? ' ' . $this->line_count : '';
        return $starting_line . $line_count . ($description !== '' ? ' ' . $description : '');
    }
}