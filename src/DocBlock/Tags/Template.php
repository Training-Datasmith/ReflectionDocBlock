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
use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Exception\Cannot_Create_Tag;
use Php_Documentor\Reflection\Type;
/**
 * Reflection class for a {@}template tag in a Docblock.
 */
final class Template extends Base_Tag
{
    /** @var non-empty-string */
    private string $template_name;
    /** @var ?Type The real type */
    private ?Type $bound;
    private ?Type $default;
    /** @param non-empty-string $templateName */
    public function __construct(string $template_name, ?Type $bound = null, ?Type $default = null, ?Description $description = null)
    {
        $this->name = 'template';
        $this->template_name = $template_name;
        $this->bound = $bound;
        $this->default = $default;
        $this->description = $description;
    }
    /**
     * @deprecated Create using static factory is deprecated,
     *  this method should not be called directly by library consumers
     */
    public static function create(string $body): ?Tag
    {
        throw new Cannot_Create_Tag('Template tag cannot be created');
    }
    public function get_template_name(): string
    {
        return $this->template_name;
    }
    public function get_bound(): ?Type
    {
        return $this->bound;
    }
    public function get_default(): ?Type
    {
        return $this->default;
    }
    public function __toString(): string
    {
        $bound = $this->bound !== null ? ' of ' . $this->bound : '';
        $default = $this->default !== null ? ' = ' . $this->default : '';
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        return $this->template_name . $bound . $default . ($description !== '' ? ' ' . $description : '');
    }
}