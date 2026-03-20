<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */
namespace Php_Documentor\Reflection\Doc_Block;

use Php_Documentor\Reflection\Doc_Block\Tags\Formatter;
interface Tag
{
    public function get_name(): string;
    /**
     * @return Tag|mixed Class that implements Tag
     * @phpstan-return ?Tag
     */
    public static function create(string $body);
    public function render(?Formatter $formatter = null): string;
    public function __toString(): string;
}