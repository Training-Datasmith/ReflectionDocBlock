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
namespace Php_Documentor\Reflection\Doc_Block\Tags;

use Php_Documentor\Reflection\Doc_Block\Description;
use Php_Documentor\Reflection\Type;
/**
 * Reflection class for a {@}throws tag in a Docblock.
 */
final class Throws extends Tag_With_Type
{
    public function __construct(Type $type, ?Description $description = null)
    {
        $this->name = 'throws';
        $this->type = $type;
        $this->description = $description;
    }
}