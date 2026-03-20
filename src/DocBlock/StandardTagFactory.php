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
namespace Php_Documentor\Reflection\Doc_Block;

use function array_key_exists;
use function array_merge;
use function array_slice;
use function call_user_func_array;
use function get_class;
use InvalidArgumentException;
use function is_object;
use Php_Documentor\Reflection\Doc_Block\Tags\Author;
use Php_Documentor\Reflection\Doc_Block\Tags\Covers;
use Php_Documentor\Reflection\Doc_Block\Tags\Deprecated;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Abstract_Php_Stan_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Extends_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Implements_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Method_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Mixin_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Param_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Property_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Property_Read_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Property_Write_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Return_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Template_Covariant_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Template_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Throws_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Factory\Var_Factory;
use Php_Documentor\Reflection\Doc_Block\Tags\Generic;
use Php_Documentor\Reflection\Doc_Block\Tags\Invalid_Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Link as LinkTag;
use Php_Documentor\Reflection\Doc_Block\Tags\See as SeeTag;
use Php_Documentor\Reflection\Doc_Block\Tags\Since;
use Php_Documentor\Reflection\Doc_Block\Tags\Source;
use Php_Documentor\Reflection\Doc_Block\Tags\Uses;
use Php_Documentor\Reflection\Doc_Block\Tags\Version;
use Php_Documentor\Reflection\Fqsen_Resolver;
use Php_Documentor\Reflection\Type_Resolver;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use function preg_match;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use function sprintf;
use function strpos;
use function trim;
use Webmozart\Assert\Assert;
/**
 * Creates a Tag object given the contents of a tag.
 *
 * This Factory is capable of determining the appropriate class for a tag and instantiate it using its `create`
 * factory method. The `create` factory method of a Tag can have a variable number of arguments; this way you can
 * pass the dependencies that you need to construct a tag object.
 *
 * > Important: each parameter in addition to the body variable for the `create` method must default to null, otherwise
 * > it violates the constraint with the interface; it is recommended to use the {@see Assert::notNull()} method to
 * > verify that a dependency is actually passed.
 *
 * This Factory also features a Service Locator component that is used to pass the right dependencies to the
 * `create` method of a tag; each dependency should be registered as a service or as a parameter.
 *
 * When you want to use a Tag of your own with custom handling you need to call the `registerTagHandler` method, pass
 * the name of the tag and a Fully Qualified Class Name pointing to a class that implements the Tag interface.
 */
final class Standard_Tag_Factory implements Tag_Factory
{
    /** PCRE regular expression matching a tag name. */
    public const REGEX_TAGNAME = '[\w\-\_\\\\:]+';
    /**
     * @var array<string, class-string<Tag>|Tag|Factory> An array with a tag as a key, and an
     *                               FQCN to a class that handles it as an array value.
     */
    private array $tag_handler_mappings = ['author' => Author::class, 'covers' => Covers::class, 'deprecated' => Deprecated::class, 'link' => Link_Tag::class, 'see' => See_Tag::class, 'since' => Since::class, 'source' => Source::class, 'uses' => Uses::class, 'version' => Version::class];
    /**
     * @var array<class-string<Tag>> An array with an annotation as a key, and an
     *      FQCN to a class that handles it as an array value.
     */
    private array $annotation_mappings = [];
    /**
     * @var ReflectionParameter[][] a lazy-loading cache containing parameters
     *      for each tagHandler that has been used.
     */
    private array $tag_handler_parameter_cache = [];
    private Fqsen_Resolver $fqsen_resolver;
    /**
     * @var mixed[] an array representing a simple Service Locator where we can store parameters and
     *     services that can be inserted into the Factory Methods of Tag Handlers.
     */
    private array $service_locator = [];
    private function __construct(Fqsen_Resolver $fqsen_resolver)
    {
        $this->fqsen_resolver = $fqsen_resolver;
        $this->add_service($fqsen_resolver, Fqsen_Resolver::class);
    }
    /**
     * Initialize this tag factory with the means to resolve an FQSEN.
     *
     * @see self::registerTagHandler() to add a new tag handler to the existing default list.
     */
    public static function create_instance(Fqsen_Resolver $fqsen_resolver): self
    {
        $tag_factory = new self($fqsen_resolver);
        $description_factory = new Description_Factory($tag_factory);
        $type_resolver = new Type_Resolver($fqsen_resolver);
        $phpstan_tag_factory = new Abstract_Php_Stan_Factory(new Param_Factory($type_resolver, $description_factory), new Var_Factory($type_resolver, $description_factory), new Return_Factory($type_resolver, $description_factory), new Property_Factory($type_resolver, $description_factory), new Property_Read_Factory($type_resolver, $description_factory), new Property_Write_Factory($type_resolver, $description_factory), new Method_Factory($type_resolver, $description_factory), new Mixin_Factory($type_resolver, $description_factory), new Implements_Factory($type_resolver, $description_factory), new Extends_Factory($type_resolver, $description_factory), new Template_Factory($type_resolver, $description_factory), new Template_Covariant_Factory($type_resolver, $description_factory), new Throws_Factory($type_resolver, $description_factory));
        $tag_factory->add_service($description_factory);
        $tag_factory->add_service($type_resolver);
        $tag_factory->register_tag_handler('param', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('var', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('return', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('property', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('property-read', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('property-write', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('method', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('mixin', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('extends', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('implements', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('template', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('template-covariant', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('template-extends', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('template-implements', $phpstan_tag_factory);
        $tag_factory->register_tag_handler('throws', $phpstan_tag_factory);
        return $tag_factory;
    }
    public function create(string $tag_line, ?Type_Context $context = null): Tag
    {
        if (!$context) {
            $context = new Type_Context('');
        }
        [$tag_name, $tag_body] = $this->extract_tag_parts($tag_line);
        return $this->create_tag(trim($tag_body), $tag_name, $context);
    }
    /**
     * @param mixed $value
     */
    public function add_parameter(string $name, $value): void
    {
        $this->service_locator[$name] = $value;
    }
    public function add_service(object $service, ?string $alias = null): void
    {
        $this->service_locator[$alias ?? get_class($service)] = $service;
    }
    /** {@inheritDoc} */
    public function register_tag_handler(string $tag_name, $handler): void
    {
        Assert::string_not_empty($tag_name);
        if (strpos($tag_name, '\\') !== false && $tag_name[0] !== '\\') {
            throw new InvalidArgumentException('A namespaced tag must have a leading backslash as it must be fully qualified');
        }
        if (is_object($handler)) {
            Assert::is_instance_of($handler, Factory::class);
            $this->tag_handler_mappings[$tag_name] = $handler;
            return;
        }
        Assert::class_exists($handler);
        Assert::implements_interface($handler, Tag::class);
        $this->tag_handler_mappings[$tag_name] = $handler;
    }
    /**
     * Extracts all components for a tag.
     *
     * @return string[]
     */
    private function extract_tag_parts(string $tag_line): array
    {
        $matches = [];
        if (!preg_match('/^@(' . self::REGEX_TAGNAME . ')((?:[\s\(\{])\s*([^\s].*)|$)/us', $tag_line, $matches)) {
            throw new InvalidArgumentException('The tag "' . $tag_line . '" does not seem to be wellformed, please check it for errors');
        }
        return array_slice($matches, 1);
    }
    /**
     * Creates a new tag object with the given name and body or returns null if the tag name was recognized but the
     * body was invalid.
     */
    private function create_tag(string $body, string $name, Type_Context $context): Tag
    {
        $handler_class_name = $this->find_handler_class_name($name, $context);
        $arguments = $this->get_arguments_for_parameters_from_wiring($this->fetch_parameters_for_handler_factory_method($handler_class_name), $this->get_service_locator_with_dynamic_parameters($context, $name, $body));
        if (array_key_exists('tagLine', $arguments)) {
            $arguments['tagLine'] = sprintf('@%s %s', $name, $body);
        }
        try {
            $callable = [$handler_class_name, 'create'];
            Assert::is_callable($callable);
            /** @phpstan-var callable(string): ?Tag $callable */
            $tag = call_user_func_array($callable, $arguments);
            return $tag ?? Invalid_Tag::create($body, $name);
        } catch (InvalidArgumentException $e) {
            return Invalid_Tag::create($body, $name)->with_error($e);
        }
    }
    /**
     * Determines the Fully Qualified Class Name of the Factory or Tag (containing a Factory Method `create`).
     *
     * @return class-string<Tag>|Tag|Factory
     */
    private function find_handler_class_name(string $tag_name, Type_Context $context)
    {
        $handler_class_name = Generic::class;
        if (isset($this->tag_handler_mappings[$tag_name])) {
            $handler_class_name = $this->tag_handler_mappings[$tag_name];
        } elseif ($this->is_annotation()) {
            // TODO: Annotation support is planned for a later stage and as such is disabled for now
            $tag_name = (string) $this->fqsen_resolver->resolve($tag_name, $context);
            if (isset($this->annotation_mappings[$tag_name])) {
                $handler_class_name = $this->annotation_mappings[$tag_name];
            }
        }
        return $handler_class_name;
    }
    /**
     * Retrieves the arguments that need to be passed to the Factory Method with the given Parameters.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $locator
     *
     * @return mixed[] A series of values that can be passed to the Factory Method of the tag whose parameters
     *     is provided with this method.
     */
    private function get_arguments_for_parameters_from_wiring(array $parameters, array $locator): array
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->get_type();
            $type_hint = null;
            if ($type instanceof ReflectionNamedType) {
                $type_hint = $type->get_name();
                if ($type_hint === 'self') {
                    $declaring_class = $parameter->get_declaring_class();
                    if ($declaring_class !== null) {
                        $type_hint = $declaring_class->get_name();
                    }
                }
            }
            $parameter_name = $parameter->get_name();
            if (isset($locator[$type_hint ?? ''])) {
                $arguments[$parameter_name] = $locator[$type_hint ?? ''];
                continue;
            }
            if (isset($locator[$parameter_name])) {
                $arguments[$parameter_name] = $locator[$parameter_name];
                continue;
            }
            $arguments[$parameter_name] = null;
        }
        return $arguments;
    }
    /**
     * Retrieves a series of ReflectionParameter objects for the static 'create' method of the given
     * tag handler class name.
     *
     * @param class-string<Tag>|Tag|Factory $handler
     *
     * @return ReflectionParameter[]
     */
    private function fetch_parameters_for_handler_factory_method($handler): array
    {
        $handler_class_name = is_object($handler) ? get_class($handler) : $handler;
        if (!isset($this->tag_handler_parameter_cache[$handler_class_name])) {
            $method_reflection = new ReflectionMethod($handler_class_name, 'create');
            $this->tag_handler_parameter_cache[$handler_class_name] = $method_reflection->get_parameters();
        }
        return $this->tag_handler_parameter_cache[$handler_class_name];
    }
    /**
     * Returns a copy of this class' Service Locator with added dynamic parameters,
     * such as the tag's name, body and Context.
     *
     * @param TypeContext $context The Context (namespace and aliases) that may be
     *  passed and is used to resolve FQSENs.
     * @param string      $tagName The name of the tag that may be
     *  passed onto the factory method of the Tag class.
     * @param string      $tagBody The body of the tag that may be
     *  passed onto the factory method of the Tag class.
     *
     * @return mixed[]
     */
    private function get_service_locator_with_dynamic_parameters(Type_Context $context, string $tag_name, string $tag_body): array
    {
        return array_merge($this->service_locator, ['name' => $tag_name, 'body' => $tag_body, Type_Context::class => $context]);
    }
    /**
     * Returns whether the given tag belongs to an annotation.
     *
     * @todo this method should be populated once we implement Annotation notation support.
     */
    private function is_annotation(): bool
    {
        // 1. Contains a namespace separator
        // 2. Contains parenthesis
        // 3. Is present in a list of known annotations (make the algorithm smart by first checking is the last part
        //    of the annotation class name matches the found tag name
        return false;
    }
}