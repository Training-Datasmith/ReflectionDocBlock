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
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use Php_Documentor\Reflection\Utils;
use Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}link tag in a Docblock.
 */
final class Link extends Base_Tag
{
    protected string $name = 'link';
    private string $link;
    /**
     * Initializes a link to a URL.
     */
    public function __construct(string $link, ?Description $description = null)
    {
        $this->link = $link;
        $this->description = $description;
    }
    public static function create(string $body, ?Description_Factory $description_factory = null, ?Type_Context $context = null): self
    {
        Assert::not_null($description_factory);
        $parts = Utils::preg_split('/\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $description_factory->create($parts[1], $context) : null;
        return new static($parts[0], $description);
    }
    /**
     * Gets the link
     */
    public function get_link(): string
    {
        return $this->link;
    }
    /**
     * Returns a string representation for this tag.
     */
    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $link = $this->link;
        return $link . ($description !== '' ? ($link !== '' ? ' ' : '') . $description : '');
    }
}