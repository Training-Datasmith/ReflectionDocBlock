<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use function array_map;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Void_;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;

use function trim;

use Webmozart\Assert\Assert;

/**
 * @internal This class is not part of the BC promise of this library.
 */
final class MethodFactory implements PHPStanFactory
{
    private DescriptionFactory $descriptionFactory;
    private TypeResolver $typeResolver;

    public function __construct(TypeResolver $typeResolver, DescriptionFactory $descriptionFactory)
    {
        $this->descriptionFactory = $descriptionFactory;
        $this->typeResolver = $typeResolver;
    }

    public function create(PhpDocTagNode $node, Context $context): Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, MethodTagValueNode::class);

        return new Method(
            $tagValue->methodName,
            array_map(
                fn (MethodTagValueParameterNode $param) => new MethodParameter(
                    trim($param->parameterName, '$'),
                    $param->type === null ? new Mixed_() : $this->typeResolver->createType(
                        $param->type,
                        $context
                    ),
                    $param->isReference,
                    $param->isVariadic,
                    $param->defaultValue === null ?
                        MethodParameter::NO_DEFAULT_VALUE :
                        (string) $param->defaultValue
                ),
                $tagValue->parameters
            ),
            $this->createReturnType($tagValue, $context),
            $tagValue->isStatic,
            $this->descriptionFactory->create($tagValue->description, $context),
            false,
        );
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof MethodTagValueNode;
    }

    private function createReturnType(MethodTagValueNode $tagValue, Context $context): Type
    {
        if ($tagValue->returnType === null) {
            return new Void_();
        }

        return $this->typeResolver->createType($tagValue->returnType, $context);
    }
}
