<?php

declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags;

use function array_map;
use Closure;
use Exception;
use function get_class;
use function get_resource_type;
use function is_array;
use function is_object;
use function is_resource;
use const PHP_VERSION_ID;
use Php_Documentor\Reflection\Doc_Block\Tag;
use ReflectionClass;
use Reflection_Exception;
use ReflectionFunction;
use function sprintf;
use Throwable;
/**
 * This class represents an exception during the tag creation
 *
 * Since the internals of the library are relaying on the correct syntax of a docblock
 * we cannot simply throw exceptions at all time because the exceptions will break the creation of a
 * docklock. Just silently ignore the exceptions is not an option because the user as an issue to fix.
 *
 * This tag holds that error information until a using application is able to display it. The object will just behave
 * like any normal tag. So the normal application flow will not break.
 */
final class Invalid_Tag implements Tag
{
    private string $name;
    private string $body;
    private ?Throwable $throwable = null;
    private function __construct(string $name, string $body)
    {
        $this->name = $name;
        $this->body = $body;
    }
    public function get_exception(): ?Throwable
    {
        return $this->throwable;
    }
    public function get_name(): string
    {
        return $this->name;
    }
    public static function create(string $body, string $name = ''): self
    {
        return new self($name, $body);
    }
    public function with_error(Throwable $exception): self
    {
        $this->flatten_exception_backtrace($exception);
        $tag = new self($this->name, $this->body);
        $tag->throwable = $exception;
        return $tag;
    }
    /**
     * Removes all complex types from backtrace
     *
     * Not all objects are serializable. So we need to remove them from the
     * stored exception to be sure that we do not break existing library usage.
     */
    private function flatten_exception_backtrace(Throwable $exception): void
    {
        $trace_property = (new ReflectionClass(Exception::class))->get_property('trace');
        if (PHP_VERSION_ID < 80100) {
            $trace_property->set_accessible(true);
        }
        do {
            $trace = $exception->get_trace();
            if (isset($trace[0]['args'])) {
                $trace = array_map(function (array $call): array {
                    $call['args'] = array_map([$this, 'flattenArguments'], $call['args'] ?? []);
                    return $call;
                }, $trace);
            }
            $trace_property->set_value($exception, $trace);
            $exception = $exception->get_previous();
        } while ($exception !== null);
        if (PHP_VERSION_ID >= 80100) {
            return;
        }
        $trace_property->set_accessible(false);
    }
    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    private function flatten_arguments($value)
    {
        if ($value instanceof Closure) {
            $closure_reflection = new ReflectionFunction($value);
            $value = sprintf('(Closure at %s:%s)', $closure_reflection->get_file_name(), $closure_reflection->get_start_line());
        } elseif (is_object($value)) {
            $value = sprintf('object(%s)', get_class($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        } elseif (is_array($value)) {
            $value = array_map([$this, 'flattenArguments'], $value);
        }
        return $value;
    }
    public function render(?Formatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new Formatter\Passthrough_Formatter();
        }
        return $formatter->format($this);
    }
    public function __toString(): string
    {
        return $this->body;
    }
}