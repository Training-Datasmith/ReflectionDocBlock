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
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use function preg_match;
use Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}deprecated tag in a Docblock.
 */
final class Deprecated extends Base_Tag
{
    protected string $name = 'deprecated';
    /**
     * PCRE regular expression matching a version vector.
     * Assumes the "x" modifier.
     */
    public const REGEX_VECTOR = '(?:
        # Normal release vectors.
        \d\S*
        |
        # VCS version vectors. Per PHPCS, they are expected to
        # follow the form of the VCS name, followed by ":", followed
        # by the version vector itself.
        # By convention, popular VCSes like CVS, SVN and GIT use "$"
        # around the actual version vector.
        [^\s\:]+\:\s*\$[^\$]+\$
    )';
    /** @var string|null The version vector. */
    private ?string $version = null;
    public function __construct(?string $version = null, ?Description $description = null)
    {
        Assert::null_or_not_empty($version);
        $this->version = $version;
        $this->description = $description;
    }
    public static function create(?string $body, ?Description_Factory $description_factory = null, ?Type_Context $context = null): self
    {
        if ($body === null || $body === '') {
            return new static();
        }
        $matches = [];
        if (!preg_match('/^(' . self::REGEX_VECTOR . ')\s*(.+)?$/sux', $body, $matches)) {
            return new static(null, $description_factory !== null ? $description_factory->create($body, $context) : null);
        }
        Assert::not_null($description_factory);
        return new static($matches[1], $description_factory->create($matches[2] ?? '', $context));
    }
    /**
     * Gets the version section of the tag.
     */
    public function get_version(): ?string
    {
        return $this->version;
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
        $version = (string) $this->version;
        return $version . ($description !== '' ? ($version !== '' ? ' ' : '') . $description : '');
    }
}