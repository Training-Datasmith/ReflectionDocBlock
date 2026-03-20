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

use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Formatter;
use function trim;
class Passthrough_Formatter implements Formatter
{
    /**
     * Formats the given tag to return a simple plain text version.
     */
    public function format(Tag $tag): string
    {
        return trim('@' . $tag->get_name() . ' ' . $tag);
    }
}