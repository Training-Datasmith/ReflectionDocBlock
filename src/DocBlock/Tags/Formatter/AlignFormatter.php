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
namespace Php_Documentor\Reflection\Doc_Block\Tags\Formatter;

use function max;
use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Formatter;
use function str_repeat;
use function strlen;
class Align_Formatter implements Formatter
{
    /** @var int The maximum tag name length. */
    protected int $max_len = 0;
    /**
     * @param Tag[] $tags All tags that should later be aligned with the formatter.
     */
    public function __construct(array $tags)
    {
        foreach ($tags as $tag) {
            $this->max_len = max($this->max_len, strlen($tag->get_name()));
        }
    }
    /**
     * Formats the given tag to return a simple plain text version.
     */
    public function format(Tag $tag): string
    {
        return '@' . $tag->get_name() . str_repeat(' ', $this->max_len - strlen($tag->get_name()) + 1) . $tag;
    }
}