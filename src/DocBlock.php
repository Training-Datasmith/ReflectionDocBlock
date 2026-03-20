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
namespace Php_Documentor\Reflection;

use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Tag_With_Type;
use Webmozart\Assert\Assert;
final class Doc_Block
{
    /** @var string The opening line for this docblock. */
    private string $summary;
    /** @var DocBlock\Description The actual description for this docblock. */
    private Doc_Block\Description $description;
    /** @var Tag[] An array containing all the tags in this docblock; except inline. */
    private array $tags = [];
    /** @var Types\Context|null Information about the context of this DocBlock. */
    private ?Types\Context $context = null;
    /** @var Location|null Information about the location of this DocBlock. */
    private ?Location $location = null;
    /** @var bool Is this DocBlock (the start of) a template? */
    private bool $is_template_start;
    /** @var bool Does this DocBlock signify the end of a DocBlock template? */
    private bool $is_template_end;
    /**
     * @param DocBlock\Tag[] $tags
     * @param Types\Context  $context  The context in which the DocBlock occurs.
     * @param Location       $location The location within the file that this DocBlock occurs in.
     */
    public function __construct(string $summary = '', ?Doc_Block\Description $description = null, array $tags = [], ?Types\Context $context = null, ?Location $location = null, bool $is_template_start = false, bool $is_template_end = false)
    {
        Assert::all_is_instance_of($tags, Tag::class);
        $this->summary = $summary;
        $this->description = $description ?: new Doc_Block\Description('');
        foreach ($tags as $tag) {
            $this->add_tag($tag);
        }
        $this->context = $context;
        $this->location = $location;
        $this->is_template_end = $is_template_end;
        $this->is_template_start = $is_template_start;
    }
    public function get_summary(): string
    {
        return $this->summary;
    }
    public function get_description(): Doc_Block\Description
    {
        return $this->description;
    }
    /**
     * Returns the current context.
     */
    public function get_context(): ?Types\Context
    {
        return $this->context;
    }
    /**
     * Returns the current location.
     */
    public function get_location(): ?Location
    {
        return $this->location;
    }
    /**
     * Returns whether this DocBlock is the start of a Template section.
     *
     * A Docblock may serve as template for a series of subsequent DocBlocks. This is indicated by a special marker
     * (`#@+`) that is appended directly after the opening `/**` of a DocBlock.
     *
     * An example of such an opening is:
     *
     * ```
     * /**#@+
     *  * My DocBlock
     *  * /
     * ```
     *
     * The description and tags (not the summary!) are copied onto all subsequent DocBlocks and also applied to all
     * elements that follow until another DocBlock is found that contains the closing marker (`#@-`).
     *
     * @see self::isTemplateEnd() for the check whether a closing marker was provided.
     */
    public function is_template_start(): bool
    {
        return $this->is_template_start;
    }
    /**
     * Returns whether this DocBlock is the end of a Template section.
     *
     * @see self::isTemplateStart() for a more complete description of the Docblock Template functionality.
     */
    public function is_template_end(): bool
    {
        return $this->is_template_end;
    }
    /**
     * Returns the tags for this DocBlock.
     *
     * @return Tag[]
     */
    public function get_tags(): array
    {
        return $this->tags;
    }
    /**
     * Returns an array of tags matching the given name. If no tags are found
     * an empty array is returned.
     *
     * @param string $name String to search by.
     *
     * @return Tag[]
     */
    public function get_tags_by_name(string $name): array
    {
        $result = [];
        foreach ($this->get_tags() as $tag) {
            if ($tag->get_name() !== $name) {
                continue;
            }
            $result[] = $tag;
        }
        return $result;
    }
    /**
     * Returns an array of tags with type matching the given name. If no tags are found
     * an empty array is returned.
     *
     * @param string $name String to search by.
     *
     * @return TagWithType[]
     */
    public function get_tags_with_type_by_name(string $name): array
    {
        $result = [];
        foreach ($this->get_tags_by_name($name) as $tag) {
            if (!$tag instanceof Tag_With_Type) {
                continue;
            }
            $result[] = $tag;
        }
        return $result;
    }
    /**
     * Checks if a tag of a certain type is present in this DocBlock.
     *
     * @param string $name Tag name to check for.
     */
    public function has_tag(string $name): bool
    {
        foreach ($this->get_tags() as $tag) {
            if ($tag->get_name() === $name) {
                return true;
            }
        }
        return false;
    }
    /**
     * Remove a tag from this DocBlock.
     *
     * @param Tag $tagToRemove The tag to remove.
     */
    public function remove_tag(Tag $tag_to_remove): void
    {
        foreach ($this->tags as $key => $tag) {
            if ($tag === $tag_to_remove) {
                unset($this->tags[$key]);
                break;
            }
        }
    }
    /**
     * Adds a tag to this DocBlock.
     *
     * @param Tag $tag The tag to add.
     */
    private function add_tag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }
}