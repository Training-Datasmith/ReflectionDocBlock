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
use Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}var tag in a Docblock.
 */
final class Var_ extends Tag_With_Type
{
    protected ?string $variable_name = '';
    public function __construct(?string $variable_name, ?Type $type = null, ?Description $description = null)
    {
        Assert::string($variable_name);
        $this->name = 'var';
        $this->variable_name = $variable_name;
        $this->type = $type;
        $this->description = $description;
    }
    /**
     * Returns the variable's name.
     */
    public function get_variable_name(): ?string
    {
        return $this->variable_name;
    }
    /**
     * Returns a string representation for this tag.
     */
    public function __toString(): string
    {
        if ($this->description !== null) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        if ($this->variable_name !== null && $this->variable_name !== '') {
            $variable_name = '$' . $this->variable_name;
        } else {
            $variable_name = '';
        }
        $type = (string) $this->type;
        return $type . ($variable_name !== '' ? ($type !== '' ? ' ' : '') . $variable_name : '') . ($description !== '' ? ($type !== '' || $variable_name !== '' ? ' ' : '') . $description : '');
    }
}