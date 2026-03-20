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
namespace Php_Documentor\Reflection\Doc_Block\Tags;

use Php_Documentor\Reflection\Doc_Block;
use Php_Documentor\Reflection\Doc_Block\Description;
/**
 * Parses a tag definition for a DocBlock.
 */
abstract class Base_Tag implements Doc_Block\Tag
{
    /** @var string Name of the tag */
    protected string $name = '';
    /** @var Description|null Description of the tag. */
    protected ?Description $description = null;
    /**
     * Gets the name of this tag.
     *
     * @return string The name of this tag.
     */
    public function get_name(): string
    {
        return $this->name;
    }
    public function get_description(): ?Description
    {
        return $this->description;
    }
    public function render(?Formatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new Formatter\Passthrough_Formatter();
        }
        return $formatter->format($this);
    }
}