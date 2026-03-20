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
use Php_Documentor\Reflection\Fqsen;
use Php_Documentor\Reflection\Fqsen_Resolver;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use Php_Documentor\Reflection\Utils;
use Webmozart\Assert\Assert;
/**
 * Reflection class for a @covers tag in a Docblock.
 */
final class Covers extends Base_Tag
{
    protected string $name = 'covers';
    private Fqsen $refers;
    /**
     * Initializes this tag.
     */
    public function __construct(Fqsen $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    public static function create(string $body, ?Description_Factory $description_factory = null, ?Fqsen_Resolver $resolver = null, ?Type_Context $context = null): self
    {
        Assert::string_not_empty($body);
        Assert::not_null($description_factory);
        Assert::not_null($resolver);
        $parts = Utils::preg_split('/\s+/Su', $body, 2);
        return new static(self::resolve_fqsen($parts[0], $resolver, $context), $description_factory->create($parts[1] ?? '', $context));
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
     * Returns the structural element this tag refers to.
     */
    public function get_reference(): Fqsen
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