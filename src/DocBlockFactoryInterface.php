<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection;

use Php_Documentor\Reflection\Doc_Block\Tag;
// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming.SuperfluousSuffix
interface Doc_Block_Factory_Interface
{
    /**
     * Factory method for easy instantiation.
     *
     * @param array<string, class-string<Tag>> $additionalTags
     */
    public static function create_instance(array $additional_tags = []): self;
    /**
     * @param string|object $docblock
     */
    public function create($docblock, ?Types\Context $context = null, ?Location $location = null): Doc_Block;
}