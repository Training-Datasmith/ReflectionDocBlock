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

use function array_key_exists;
use function explode;
use Php_Documentor\Reflection\Doc_Block\Description;
use Php_Documentor\Reflection\Doc_Block\Description_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Reference\Fqsen as FqsenRef;
use Php_Documentor\Reflection\Doc_Block\Tags\Reference\Reference;
use Php_Documentor\Reflection\Doc_Block\Tags\Reference\Url;
use Php_Documentor\Reflection\Fqsen;
use Php_Documentor\Reflection\Fqsen_Resolver;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use Php_Documentor\Reflection\Utils;
use function preg_match;
use Webmozart\Assert\Assert;
/**
 * Reflection class for an {@}see tag in a Docblock.
 */
final class See extends Base_Tag
{
    protected string $name = 'see';
    protected Reference $refers;
    /**
     * Initializes this tag.
     */
    public function __construct(Reference $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    public static function create(string $body, ?Fqsen_Resolver $type_resolver = null, ?Description_Factory $description_factory = null, ?Type_Context $context = null): self
    {
        Assert::not_null($description_factory);
        $parts = Utils::preg_split('/\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $description_factory->create($parts[1], $context) : null;
        // https://tools.ietf.org/html/rfc2396#section-3
        if (preg_match('#\w://\w#', $parts[0])) {
            return new static(new Url($parts[0]), $description);
        }
        return new static(new Fqsen_Ref(self::resolve_fqsen($parts[0], $type_resolver, $context)), $description);
    }
    private static function resolve_fqsen(string $parts, ?Fqsen_Resolver $fqsen_resolver, ?Type_Context $context): Fqsen
    {
        Assert::not_null($fqsen_resolver);
        $fqsen_parts = explode('::', $parts);
        $resolved = $fqsen_resolver->resolve($fqsen_parts[0], $context);
        if (!array_key_exists(1, $fqsen_parts)) {
            return $resolved;
        }
        return new Fqsen($resolved . '::' . $fqsen_parts[1]);
    }
    /**
     * Returns the ref of this tag.
     */
    public function get_reference(): Reference
    {
        return $this->refers;
    }
    /**
     * Returns a string representation of this tag.
     */
    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $refers = (string) $this->refers;
        return $refers . ($description !== '' ? ($refers !== '' ? ' ' : '') . $description : '');
    }
}