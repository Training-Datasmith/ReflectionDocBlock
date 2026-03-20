<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags\Factory;

use function is_string;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Invalid_Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Param;
use Php_Documentor\Reflection\Exception\Parser_Exception;
use Php_Documentor\Reflection\Type_Resolver;
use Php_Documentor\Reflection\Types\Context;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Invalid_Tag_Value_Node;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Param_Tag_Value_Node;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Php_Doc_Tag_Node;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Typeless_Param_Tag_Value_Node;
use Php_Stan\Php_Doc_Parser\Ast\Type\Identifier_Type_Node;
use Php_Stan\Php_Doc_Parser\Ast\Type\Offset_Access_Type_Node;
use function trim;
use Webmozart\Assert\Assert;
/**
 * @internal This class is not part of the BC promise of this library.
 */
final class Param_Factory implements Php_Stan_Factory
{
    private Description_Factory $description_factory;
    private Type_Resolver $type_resolver;
    public function __construct(Type_Resolver $type_resolver, Description_Factory $description_factory)
    {
        $this->description_factory = $description_factory;
        $this->type_resolver = $type_resolver;
    }
    public function create(Php_Doc_Tag_Node $node, Context $context): Tag
    {
        $tag_value = $node->value;
        if ($tag_value instanceof Invalid_Tag_Value_Node) {
            return Invalid_Tag::create($tag_value->value, 'param')->with_error(Parser_Exception::from($tag_value->exception));
        }
        Assert::is_instance_of_any($tag_value, [Param_Tag_Value_Node::class, Typeless_Param_Tag_Value_Node::class]);
        if (($tag_value->type ?? null) instanceof Offset_Access_Type_Node) {
            return Invalid_Tag::create((string) $tag_value, 'param');
        }
        $description = $tag_value->get_attribute('description');
        if (is_string($description) === false) {
            $description = $tag_value->description;
        }
        return new Param(trim($tag_value->parameter_name, '$'), $this->type_resolver->create_type($tag_value->type ?? new Identifier_Type_Node('mixed'), $context), $tag_value->is_variadic, $this->description_factory->create($description, $context), $tag_value->is_reference);
    }
    public function supports(Php_Doc_Tag_Node $node, Context $context): bool
    {
        return $node->value instanceof Param_Tag_Value_Node || $node->value instanceof Typeless_Param_Tag_Value_Node || $node->name === '@param';
    }
}