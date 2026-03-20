<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */
namespace Php_Documentor\Reflection\Doc_Block\Tags\Reference;

use Webmozart\Assert\Assert;
/**
 * Url reference used by {@see \phpDocumentor\Reflection\DocBlock\Tags\See}
 */
final class Url implements Reference
{
    private string $uri;
    public function __construct(string $uri)
    {
        Assert::string_not_empty($uri);
        $this->uri = $uri;
    }
    public function __toString(): string
    {
        return $this->uri;
    }
}