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

use function implode;
use Php_Documentor\Reflection\Doc_Block\Description;
use Php_Documentor\Reflection\Exception\Cannot_Create_Tag;
use Php_Documentor\Reflection\Type;
use Php_Documentor\Reflection\Types\Void_;
use Webmozart\Assert\Assert;
/**
 * Reflection class for an {@}method in a Docblock.
 */
final class Method extends Base_Tag
{
    protected string $name = 'method';
    private string $method_name;
    private bool $is_static;
    private Type $return_type;
    private bool $returns_reference;
    /** @var MethodParameter[] */
    private array $parameters;
    /**
     * @param MethodParameter[] $parameters
     */
    public function __construct(string $method_name, array $parameters = [], ?Type $return_type = null, bool $static = false, ?Description $description = null, bool $returns_reference = false)
    {
        Assert::string_not_empty($method_name);
        if ($return_type === null) {
            $return_type = new Void_();
        }
        $this->method_name = $method_name;
        $this->return_type = $return_type;
        $this->is_static = $static;
        $this->description = $description;
        $this->returns_reference = $returns_reference;
        $this->parameters = $parameters;
    }
    /**
     * Retrieves the method name.
     */
    public function get_method_name(): string
    {
        return $this->method_name;
    }
    /** @return MethodParameter[] */
    public function get_parameters(): array
    {
        return $this->parameters;
    }
    /**
     * Checks whether the method tag describes a static method or not.
     *
     * @return bool TRUE if the method declaration is for a static method, FALSE otherwise.
     */
    public function is_static(): bool
    {
        return $this->is_static;
    }
    public function get_return_type(): Type
    {
        return $this->return_type;
    }
    public function returns_reference(): bool
    {
        return $this->returns_reference;
    }
    public function __toString(): string
    {
        $arguments = [];
        foreach ($this->parameters as $parameter) {
            $arguments[] = (string) $parameter;
        }
        $argument_str = '(' . implode(', ', $arguments) . ')';
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $static = $this->is_static ? 'static' : '';
        $return_type = (string) $this->return_type;
        $method_name = $this->method_name;
        $reference = $this->returns_reference ? '&' : '';
        return $static . ($return_type !== '' ? ($static !== '' ? ' ' : '') . $return_type : '') . ($method_name !== '' ? ($static !== '' || $return_type !== '' ? ' ' : '') . $reference . $method_name : '') . $argument_str . ($description !== '' ? ' ' . $description : '');
    }
    public static function create(string $body): void
    {
        throw new Cannot_Create_Tag('Method tag cannot be created');
    }
}