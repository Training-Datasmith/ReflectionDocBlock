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
use Php_Documentor\Reflection\Type;
/**
 * Reflection class for the {@}param tag in a Docblock.
 */
final class Param extends Tag_With_Type
{
    private ?string $variable_name = null;
    /** @var bool determines whether this is a variadic argument */
    private bool $is_variadic;
    /** @var bool determines whether this is passed by reference */
    private bool $is_reference;
    public function __construct(?string $variable_name, ?Type $type = null, bool $is_variadic = false, ?Description $description = null, bool $is_reference = false)
    {
        $this->name = 'param';
        $this->variable_name = $variable_name;
        $this->type = $type;
        $this->is_variadic = $is_variadic;
        $this->description = $description;
        $this->is_reference = $is_reference;
    }
    /**
     * Returns the variable's name.
     */
    public function get_variable_name(): ?string
    {
        return $this->variable_name;
    }
    /**
     * Returns whether this tag is variadic.
     */
    public function is_variadic(): bool
    {
        return $this->is_variadic;
    }
    /**
     * Returns whether this tag is passed by reference.
     */
    public function is_reference(): bool
    {
        return $this->is_reference;
    }
    /**
     * Returns a string representation for this tag.
     */
    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $variable_name = '';
        if ($this->variable_name !== null && $this->variable_name !== '') {
            $variable_name .= ($this->is_reference ? '&' : '') . ($this->is_variadic ? '...' : '');
            $variable_name .= '$' . $this->variable_name;
        }
        $type = (string) $this->type;
        return $type . ($variable_name !== '' ? ($type !== '' ? ' ' : '') . $variable_name : '') . ($description !== '' ? ($type !== '' || $variable_name !== '' ? ' ' : '') . $description : '');
    }
}