<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags\Factory;

use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Types\Context;
use Php_Stan\Php_Doc_Parser\Ast\Php_Doc\Php_Doc_Tag_Node;
interface Php_Stan_Factory
{
    public function create(Php_Doc_Tag_Node $node, Context $context): Tag;
    public function supports(Php_Doc_Tag_Node $node, Context $context): bool;
}