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
namespace Php_Documentor\Reflection\Doc_Block\Tags\Factory;

use function array_key_last;
use function get_class;
use function gettype;
use function method_exists;
use function ucfirst;
use function var_export;
/**
 * @internal This class is not part of the BC promise of this library.
 */
final class Method_Parameter_Factory
{
    /**
     * Formats the given default value to a string-able mixin
     *
     * @param mixed $defaultValue
     */
    public function format($default_value): string
    {
        $method = 'format' . ucfirst(gettype($default_value));
        if (method_exists($this, $method)) {
            return $this->{$method}($default_value);
        }
        return '';
    }
    private function format_double(float $default_value): string
    {
        return var_export($default_value, true);
    }
    private function format_null(): string
    {
        return 'null';
    }
    private function format_integer(int $default_value): string
    {
        return var_export($default_value, true);
    }
    private function format_string(string $default_value): string
    {
        return var_export($default_value, true);
    }
    private function format_boolean(bool $default_value): string
    {
        return var_export($default_value, true);
    }
    /**
     * @param array<(array<mixed>|int|float|bool|string|object|null)> $defaultValue
     */
    private function format_array(array $default_value): string
    {
        $formated_value = '[';
        foreach ($default_value as $key => $value) {
            $method = 'format' . ucfirst(gettype($value));
            if (!method_exists($this, $method)) {
                continue;
            }
            $formated_value .= $this->{$method}($value);
            if ($key === array_key_last($default_value)) {
                continue;
            }
            $formated_value .= ',';
        }
        return $formated_value . ']';
    }
    private function format_object(object $default_value): string
    {
        return 'new ' . get_class($default_value) . '()';
    }
}