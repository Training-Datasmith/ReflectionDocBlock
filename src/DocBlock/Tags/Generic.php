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

use InvalidArgumentException;
use Php_Documentor\Reflection\Doc_Block\Description;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Doc_Block\Standard_Tag_Factory;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use function preg_match;
use Webmozart\Assert\Assert;
/**
 * Parses a tag definition for a DocBlock.
 */
final class Generic extends Base_Tag
{
    /**
     * Parses a tag and populates the member variables.
     *
     * @param string      $name        Name of the tag.
     * @param Description $description The contents of the given tag.
     */
    public function __construct(string $name, ?Description $description = null)
    {
        $this->validate_tag_name($name);
        $this->name = $name;
        $this->description = $description;
    }
    /**
     * Creates a new tag that represents any unknown tag type.
     */
    public static function create(string $body, string $name = '', ?Description_Factory $description_factory = null, ?Type_Context $context = null): self
    {
        Assert::string_not_empty($name);
        Assert::not_null($description_factory);
        $description = $body !== '' ? $description_factory->create($body, $context) : null;
        return new static($name, $description);
    }
    /**
     * Returns the tag as a serialized string
     */
    public function __toString(): string
    {
        if ($this->description) {
            return $this->description->render();
        }
        return '';
    }
    /**
     * Validates if the tag name matches the expected format, otherwise throws an exception.
     */
    private function validate_tag_name(string $name): void
    {
        if (!preg_match('/^' . Standard_Tag_Factory::REGEX_TAGNAME . '$/u', $name)) {
            throw new InvalidArgumentException('The tag name "' . $name . '" is not wellformed. Tags may only consist of letters, underscores, ' . 'hyphens and backslashes.');
        }
    }
}