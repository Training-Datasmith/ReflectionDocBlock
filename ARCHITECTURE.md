# Architecture: ReflectionDocBlock

## Purpose

PHP library for parsing PHPDoc docblock strings into a structured object graph. Used by phpDocumentor, PHPStan, Psalm, and IDE tooling to extract type information and tags from source code comments.

## Directory Structure

```
src/
  Doc_Block.php               The parsed docblock: summary, description, tags array
  Doc_Block_Factory.php       Parses a raw docblock string into a DocBlock object
  Doc_Block_Factory_Interface.php
  DocBlock/
    Description.php           Multi-line description with inline tag support
    Description_Factory.php   Parses description text, resolving inline {@link} etc.
    Serializer.php            Re-serialises a DocBlock back to a formatted string
    Standard_Tag_Factory.php  Maps tag names to Tag subclasses
    Tag.php                   Base tag interface
    Tag_Factory.php           Tag factory interface
    Tags/
      Author.php, Deprecated.php, Param.php, Return_.php, Throws.php, Var_.php, etc.
      Base_Tag.php            Common tag rendering
      Factory/                Per-tag factory classes that parse tag body strings
      Formatter/              Serialization formatters (aligned, passthrough)
      Reference/              FQSEN and URL reference value objects
  Exception/                  Cannot_Create_Tag, Parser_Exception, Pcre_Exception
  Utils.php                   FQSEN normalisation utilities
```

## Key Design Decisions

- **Tag type registry**: The `Standard_Tag_Factory` maps tag names to factory classes. Unknown tags become `Generic` tags preserving the raw body, so docblocks with custom tags are never rejected.
- **Separate factories**: Each tag has its own `Factory` class so parsing logic is isolated and testable independently.
- **Round-trip serialisation**: The `Serializer` can reproduce a formatted docblock from the parsed object graph, enabling docblock rewriting tools.
- **FQSEN resolution**: Type references in `@param`, `@return`, etc. are resolved to fully qualified class names using a configurable resolver.

## Extension Points

- Register custom tag factories via `StandardTagFactory::addService()`.
- Implement `TagFactory\Factory` for a new tag type with custom body parsing.
- Implement `DocBlockFactoryInterface` to replace the parsing strategy entirely.

## Dependency Flow

```
raw docblock string
  -> DocBlockFactory::create(docblock, context)
    -> summary extraction (first sentence)
    -> DescriptionFactory::create(remaining text)
    -> foreach @tag line: TagFactory::create(tagName, body, context)
      -> Specific Tag::create(body, context)  (e.g., Param::create)
    -> DocBlock(summary, description, tags[])
```
