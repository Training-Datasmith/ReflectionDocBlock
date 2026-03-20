<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags\Factory;

use function is_string;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Throws;
use Php_Documentor\Reflection\Type_Resolver;
use Php_Documentor\Reflection\Types\Context;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Php_Doc_Tag_Node;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Throws_Tag_Value_Node;
use Webmozart\Assert\Assert;
/**
 * @internal This class is not part of the BC promise of this library.
 */
final class Throws_Factory implements Php_Stan_Factory
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
        Assert::is_instance_of($tag_value, Throws_Tag_Value_Node::class);
        $description = $tag_value->get_attribute('description');
        if (is_string($description) === false) {
            $description = $tag_value->description;
        }
        return new Throws($this->type_resolver->create_type($tag_value->type, $context), $this->description_factory->create($description, $context));
    }
    public function supports(Php_Doc_Tag_Node $node, Context $context): bool
    {
        return $node->value instanceof Throws_Tag_Value_Node;
    }
}