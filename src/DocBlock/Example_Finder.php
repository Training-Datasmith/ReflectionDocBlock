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
namespace Php_Documentor\Reflection\Doc_Block;

use function array_slice;
use const DIRECTORY_SEPARATOR;
use function file;
use function getcwd;
use function implode;
use function is_readable;
use Php_Documentor\Reflection\Doc_Block\Tags\Example;
use function rtrim;
use function sprintf;
use function trim;
/**
 * Class used to find an example file's location based on a given ExampleDescriptor.
 */
class Example_Finder
{
    private string $source_directory = '';
    /** @var string[] */
    private array $example_directories = [];
    /**
     * Attempts to find the example contents for the given descriptor.
     */
    public function find(Example $example): string
    {
        $filename = $example->get_file_path();
        $file = $this->get_example_file_contents($filename);
        if ($file === null) {
            return sprintf('** File not found : %s **', $filename);
        }
        return implode('', array_slice($file, $example->get_starting_line() - 1, $example->get_line_count()));
    }
    /**
     * Registers the project's root directory where an 'examples' folder can be expected.
     */
    public function set_source_directory(string $directory = ''): void
    {
        $this->source_directory = $directory;
    }
    /**
     * Returns the project's root directory where an 'examples' folder can be expected.
     */
    public function get_source_directory(): string
    {
        return $this->source_directory;
    }
    /**
     * Registers a series of directories that may contain examples.
     *
     * @param string[] $directories
     */
    public function set_example_directories(array $directories): void
    {
        $this->example_directories = $directories;
    }
    /**
     * Returns a series of directories that may contain examples.
     *
     * @return string[]
     */
    public function get_example_directories(): array
    {
        return $this->example_directories;
    }
    /**
     * Attempts to find the requested example file and returns its contents or null if no file was found.
     *
     * This method will try several methods in search of the given example file, the first one it encounters is
     * returned:
     *
     * 1. Iterates through all examples folders for the given filename
     * 2. Checks the source folder for the given filename
     * 3. Checks the 'examples' folder in the current working directory for examples
     * 4. Checks the path relative to the current working directory for the given filename
     *
     * @return string[] all lines of the example file
     */
    private function get_example_file_contents(string $filename): ?array
    {
        $normalized_path = null;
        foreach ($this->example_directories as $directory) {
            $example_file_from_config = $this->construct_example_path($directory, $filename);
            if (is_readable($example_file_from_config)) {
                $normalized_path = $example_file_from_config;
                break;
            }
        }
        if ($normalized_path === null) {
            if (is_readable($this->get_example_path_from_source($filename))) {
                $normalized_path = $this->get_example_path_from_source($filename);
            } elseif (is_readable($this->get_example_path_from_example_directory($filename))) {
                $normalized_path = $this->get_example_path_from_example_directory($filename);
            } elseif (is_readable($filename)) {
                $normalized_path = $filename;
            }
        }
        $lines = $normalized_path !== null && is_readable($normalized_path) ? file($normalized_path) : false;
        return $lines !== false ? $lines : null;
    }
    /**
     * Get example filepath based on the example directory inside your project.
     */
    private function get_example_path_from_example_directory(string $file): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . $file;
    }
    /**
     * Returns a path to the example file in the given directory..
     */
    private function construct_example_path(string $directory, string $file): string
    {
        return rtrim($directory, '\/') . DIRECTORY_SEPARATOR . $file;
    }
    /**
     * Get example filepath based on sourcecode.
     */
    private function get_example_path_from_source(string $file): string
    {
        return sprintf('%s%s%s', trim($this->get_source_directory(), '\/'), DIRECTORY_SEPARATOR, trim($file, '"'));
    }
}