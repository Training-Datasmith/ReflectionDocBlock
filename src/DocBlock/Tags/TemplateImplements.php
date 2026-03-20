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

use Php_Documentor\Reflection\Doc_Block\Description;
use Php_Documentor\Reflection\Type;
/**
 * Reflection class for a {@}template-implements tag in a Docblock.
 */
final class Template_Implements extends Implements_
{
    public function __construct(Type $type, ?Description $description = null)
    {
        parent::__construct($type, $description);
        $this->name = 'template-implements';
    }
}