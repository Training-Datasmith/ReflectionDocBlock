<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection\Exception;

use InvalidArgumentException;
final class Parser_Exception extends InvalidArgumentException implements Reflection_Docblock_Exception
{
    public static function from(\Php_Stan\Php_Doc_Parser\Parser\Parser_Exception $exception): self
    {
        return new self('Failed to parse docblock: ' . $exception->get_message(), 0, $exception);
    }
}