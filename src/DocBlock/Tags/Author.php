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

use const FILTER_VALIDATE_EMAIL;
use function filter_var;
use InvalidArgumentException;
use function preg_match;
use function trim;
/**
 * Reflection class for an {@}author tag in a Docblock.
 */
final class Author extends Base_Tag
{
    /** @var string register that this is the author tag. */
    protected string $name = 'author';
    /** @var string The name of the author */
    private string $author_name;
    /** @var string The email of the author */
    private string $author_email;
    /**
     * Initializes this tag with the author name and e-mail.
     */
    public function __construct(string $author_name, string $author_email)
    {
        if ($author_email && !filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The author tag does not have a valid e-mail address');
        }
        $this->author_name = $author_name;
        $this->author_email = $author_email;
    }
    /**
     * Gets the author's name.
     *
     * @return string The author's name.
     */
    public function get_author_name(): string
    {
        return $this->author_name;
    }
    /**
     * Returns the author's email.
     *
     * @return string The author's email.
     */
    public function get_email(): string
    {
        return $this->author_email;
    }
    /**
     * Returns this tag in string form.
     */
    public function __toString(): string
    {
        if ($this->author_email) {
            $author_email = '<' . $this->author_email . '>';
        } else {
            $author_email = '';
        }
        $author_name = $this->author_name;
        return $author_name . ($author_email !== '' ? ($author_name !== '' ? ' ' : '') . $author_email : '');
    }
    /**
     * Attempts to create a new Author object based on the tag body.
     */
    public static function create(string $body): ?self
    {
        $split_tag_content = preg_match('/^([^\<]*)(?:\<([^\>]*)\>)?$/u', $body, $matches);
        if (!$split_tag_content) {
            return null;
        }
        $author_name = trim($matches[1]);
        $email = isset($matches[2]) ? trim($matches[2]) : '';
        return new static($author_name, $email);
    }
}