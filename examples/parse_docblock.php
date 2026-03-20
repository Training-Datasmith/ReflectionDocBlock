<?php

declare(strict_types=1);

/**
 * ReflectionDocBlock — parsing a PHPDoc comment example.
 *
 * Demonstrates: parsing a docblock string, extracting summary, description, and tags.
 *
 * Run:
 *   php examples/parse_docblock.php
 */

require __DIR__ . '/../vendor/autoload.php';

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;

$factory = DocBlockFactory::createInstance();

$docComment = <<<'DOCBLOCK'
/**
 * Sends an email to one or more recipients.
 *
 * This method renders the template identified by $code and delivers it
 * via the configured transport adapter. If the email is disabled, sending
 * is skipped silently.
 *
 * @param string   $code       Email template code registered in the mailer configuration
 * @param string[] $recipients Non-empty list of recipient email addresses
 * @param array    $data       Template variables passed to the renderer
 *
 * @return void
 *
 * @throws \InvalidArgumentException If any recipient is an empty string
 *
 * @since 1.0
 */
DOCBLOCK;

$docBlock = $factory->create($docComment);

echo 'Summary:' . PHP_EOL;
echo '  ' . $docBlock->getSummary() . PHP_EOL . PHP_EOL;

echo 'Description:' . PHP_EOL;
echo '  ' . $docBlock->getDescription() . PHP_EOL . PHP_EOL;

echo 'Tags:' . PHP_EOL;
foreach ($docBlock->getTags() as $tag) {
    echo '  @' . $tag->getName();
    if (method_exists($tag, '__toString')) {
        echo ' ' . $tag;
    }
    echo PHP_EOL;
}

// Check for specific tags
if ($docBlock->hasTag('param')) {
    echo PHP_EOL . 'Parameter count: ' . count($docBlock->getTagsByName('param')) . PHP_EOL;
}

if ($docBlock->hasTag('throws')) {
    $throws = $docBlock->getTagsByName('throws');
    echo 'Throws: ' . $throws[0]->getType() . PHP_EOL;
}
