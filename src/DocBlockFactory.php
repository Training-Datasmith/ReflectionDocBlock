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

use function array_shift;
use function count;
use function explode;
use InvalidArgumentException;
use function is_object;
use LogicException;
use function method_exists;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Doc_Block\Standard_Tag_Factory;
use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tag_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Factory;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strpos;
use function substr;
use function trim;
use Webmozart\Assert\Assert;
final class Doc_Block_Factory implements Doc_Block_Factory_Interface
{
    private Doc_Block\Description_Factory $description_factory;
    private Tag_Factory $tag_factory;
    /**
     * Initializes this factory with the required subcontractors.
     */
    public function __construct(Description_Factory $description_factory, Tag_Factory $tag_factory)
    {
        $this->description_factory = $description_factory;
        $this->tag_factory = $tag_factory;
    }
    /**
     * Factory method for easy instantiation.
     *
     * @param array<string, class-string<Tag>|Factory> $additionalTags
     */
    public static function create_instance(array $additional_tags = []): Doc_Block_Factory_Interface
    {
        $fqsen_resolver = new Fqsen_Resolver();
        $tag_factory = Standard_Tag_Factory::create_instance($fqsen_resolver);
        $description_factory = new Description_Factory($tag_factory);
        $doc_block_factory = new self($description_factory, $tag_factory);
        foreach ($additional_tags as $tag_name => $tag_handler) {
            $doc_block_factory->register_tag_handler($tag_name, $tag_handler);
        }
        return $doc_block_factory;
    }
    /**
     * @param object|string $docblock A string containing the DocBlock to parse or an object supporting the
     *                                getDocComment method (such as a ReflectionClass object).
     */
    public function create($docblock, ?Types\Context $context = null, ?Location $location = null): Doc_Block
    {
        if (is_object($docblock)) {
            if (!method_exists($docblock, 'getDocComment')) {
                $exception_message = 'Invalid object passed; the given object must support the getDocComment method';
                throw new InvalidArgumentException($exception_message);
            }
            $docblock = $docblock->get_doc_comment();
            Assert::string($docblock);
        }
        Assert::string_not_empty($docblock);
        if ($context === null) {
            $context = new Types\Context('');
        }
        $parts = $this->split_doc_block($this->strip_doc_comment($docblock));
        [$template_marker, $summary, $description, $tags] = $parts;
        return new Doc_Block($summary, $description ? $this->description_factory->create($description, $context) : null, $this->parse_tag_block($tags, $context), $context, $location, $template_marker === '#@+', $template_marker === '#@-');
    }
    /**
     * @param class-string<Tag>|Factory $handler
     */
    public function register_tag_handler(string $tag_name, $handler): void
    {
        $this->tag_factory->register_tag_handler($tag_name, $handler);
    }
    /**
     * Strips the asterisks from the DocBlock comment.
     *
     * @param string $comment String containing the comment text.
     */
    private function strip_doc_comment(string $comment): string
    {
        $comment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]?(.*)?#u', '$1', $comment);
        Assert::string($comment);
        $comment = trim($comment);
        // reg ex above is not able to remove */ from a single line docblock
        if (substr($comment, -2) === '*/') {
            $comment = trim(substr($comment, 0, -2));
        }
        return str_replace(["\r\n", "\r"], "\n", $comment);
    }
    // phpcs:disable
    /**
     * Splits the DocBlock into a template marker, summary, description and block of tags.
     *
     * @param string $comment Comment to split into the sub-parts.
     *
     * @return string[] containing the template marker (if any), summary, description and a string containing the tags.
     *
     * @author Mike van Riel <me@mikevanriel.com> for extending the regex with template marker support.
     *
     * @author Richard van Velzen (@_richardJ) Special thanks to Richard for the regex responsible for the split.
     */
    private function split_doc_block(string $comment): array
    {
        // phpcs:enable
        // Performance improvement cheat: if the first character is an @ then only tags are in this DocBlock. This
        // method does not split tags so we return this verbatim as the fourth result (tags). This saves us the
        // performance impact of running a regular expression
        if (strpos($comment, '@') === 0) {
            return ['', '', '', $comment];
        }
        // clears all extra horizontal whitespace from the line endings to prevent parsing issues
        $comment = preg_replace('/\h*$/Sum', '', $comment);
        Assert::string($comment);
        /*
         * Splits the docblock into a template marker, summary, description and tags section.
         *
         * - The template marker is empty, #@+ or #@- if the DocBlock starts with either of those (a newline may
         *   occur after it and will be stripped).
         * - The short description is started from the first character until a dot is encountered followed by a
         *   newline OR two consecutive newlines (horizontal whitespace is taken into account to consider spacing
         *   errors). This is optional.
         * - The long description, any character until a new line is encountered followed by an @ and word
         *   characters (a tag). This is optional.
         * - Tags; the remaining characters
         *
         * Big thanks to RichardJ for contributing this Regular Expression
         */
        preg_match('/
            \A
            # 1. Extract the template marker
            (?:(\#\@\+|\#\@\-)\n?)?

            # 2. Extract the summary
            (?:
              (?! @\pL ) # The summary may not start with an @
              (
                [^\n.]+
                (?:
                  (?! \. \n | \n{2} )     # End summary upon a dot followed by newline or two newlines
                  [\n.]* (?! [ \t]* @\pL ) # End summary when an @ is found as first character on a new line
                  [^\n.]+                 # Include anything else
                )*
                \.?
              )?
            )

            # 3. Extract the description
            (?:
              \s*        # Some form of whitespace _must_ precede a description because a summary must be there
              (?! @\pL ) # The description may not start with an @
              (
                [^\n]+
                (?: \n+
                  (?! [ \t]* @\pL ) # End description when an @ is found as first character on a new line
                  [^\n]+            # Include anything else
                )*
              )
            )?

            # 4. Extract the tags (anything that follows)
            (\s+ [\s\S]*)? # everything that follows
            /ux', $comment, $matches);
        array_shift($matches);
        while (count($matches) < 4) {
            $matches[] = '';
        }
        return $matches;
    }
    /**
     * Creates the tag objects.
     *
     * @param string $tags Tag block to parse.
     * @param Types\Context $context Context of the parsed Tag
     *
     * @return DocBlock\Tag[]
     */
    private function parse_tag_block(string $tags, Types\Context $context): array
    {
        $tags = $this->filter_tag_block($tags);
        if ($tags === null) {
            return [];
        }
        $result = [];
        $lines = $this->split_tag_block_into_tag_lines($tags);
        foreach ($lines as $key => $tag_line) {
            $result[$key] = $this->tag_factory->create(trim($tag_line), $context);
        }
        return $result;
    }
    /**
     * @return string[]
     */
    private function split_tag_block_into_tag_lines(string $tags): array
    {
        $result = [];
        foreach (explode("\n", $tags) as $tag_line) {
            if ($tag_line !== '' && strpos($tag_line, '@') === 0) {
                $result[] = $tag_line;
            } else {
                $result[count($result) - 1] .= "\n" . $tag_line;
            }
        }
        return $result;
    }
    private function filter_tag_block(string $tags): ?string
    {
        $tags = trim($tags);
        if (!$tags) {
            return null;
        }
        if ($tags[0] !== '@') {
            // @codeCoverageIgnoreStart
            // Can't simulate this; this only happens if there is an error with the parsing of the DocBlock that
            // we didn't foresee.
            throw new LogicException('A tag block started with text instead of an at-sign(@): ' . $tags);
            // @codeCoverageIgnoreEnd
        }
        return $tags;
    }
}