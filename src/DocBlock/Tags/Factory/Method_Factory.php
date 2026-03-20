<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags\Factory;

use function array_map;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Method;
use Php_Documentor\Reflection\Doc_Block\Tags\Method_Parameter;
use Php_Documentor\Reflection\Type;
use Php_Documentor\Reflection\Type_Resolver;
use Php_Documentor\Reflection\Types\Context;
use Php_Documentor\Reflection\Types\Mixed_;
use Php_Documentor\Reflection\Types\Void_;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Method_Tag_Value_Node;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Method_Tag_Value_Parameter_Node;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Php_Doc_Tag_Node;
use function trim;
use Webmozart\Assert\Assert;
/**
 * @internal This class is not part of the BC promise of this library.
 */
final class Method_Factory implements Php_Stan_Factory
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
        Assert::is_instance_of($tag_value, Method_Tag_Value_Node::class);
        return new Method($tag_value->method_name, array_map(fn(Method_Tag_Value_Parameter_Node $param) => new Method_Parameter(trim($param->parameter_name, '$'), $param->type === null ? new Mixed_() : $this->type_resolver->create_type($param->type, $context), $param->is_reference, $param->is_variadic, $param->default_value === null ? Method_Parameter::NO_DEFAULT_VALUE : (string) $param->default_value), $tag_value->parameters), $this->create_return_type($tag_value, $context), $tag_value->is_static, $this->description_factory->create($tag_value->description, $context), false);
    }
    public function supports(Php_Doc_Tag_Node $node, Context $context): bool
    {
        return $node->value instanceof Method_Tag_Value_Node;
    }
    private function create_return_type(Method_Tag_Value_Node $tag_value, Context $context): Type
    {
        if ($tag_value->return_type === null) {
            return new Void_();
        }
        return $this->type_resolver->create_type($tag_value->return_type, $context);
    }
}