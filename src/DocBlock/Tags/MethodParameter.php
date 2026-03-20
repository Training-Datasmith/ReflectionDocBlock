<?php

/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 */
declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags;

use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Method_Parameter_Factory;
use Php_Documentor\Reflection\Type;
final class Method_Parameter
{
    private Type $type;
    private bool $is_reference;
    private bool $is_variadic;
    private string $name;
    /** @var mixed */
    private $default_value;
    public const NO_DEFAULT_VALUE = '__NO_VALUE__';
    /**
     * @param mixed $defaultValue
     */
    public function __construct(string $name, Type $type, bool $is_reference = false, bool $is_variadic = false, $default_value = self::NO_DEFAULT_VALUE)
    {
        $this->type = $type;
        $this->is_reference = $is_reference;
        $this->is_variadic = $is_variadic;
        $this->name = $name;
        $this->default_value = $default_value;
    }
    public function get_name(): string
    {
        return $this->name;
    }
    public function get_type(): Type
    {
        return $this->type;
    }
    public function is_reference(): bool
    {
        return $this->is_reference;
    }
    public function is_variadic(): bool
    {
        return $this->is_variadic;
    }
    public function get_default_value(): ?string
    {
        if ($this->default_value === self::NO_DEFAULT_VALUE) {
            return null;
        }
        return (new Method_Parameter_Factory())->format($this->default_value);
    }
    public function __toString(): string
    {
        return $this->get_type() . ' ' . ($this->is_reference() ? '&' : '') . ($this->is_variadic() ? '...' : '') . '$' . $this->get_name() . ($this->default_value !== self::NO_DEFAULT_VALUE ? ' = ' . (new Method_Parameter_Factory())->format($this->default_value) : '');
    }
}