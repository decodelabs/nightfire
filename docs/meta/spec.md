# Nightfire — Package Specification

> **Cluster:** `content`
> **Language:** `php`
> **Milestone:** `m1`
> **Repo:** `https://github.com/decodelabs/nightfire`
> **Role:** Composable view blocks framework

This document describes the purpose, contracts, and design of **Nightfire** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Nightfire in their own applications or libraries.
- Contributors **maintaining or extending** Nightfire.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Nightfire provides a framework for creating, managing, and composing view blocks—reusable, serializable content components that can be organized into areas and rendered as markup. It decouples block definition from serialization format, supporting both JSON and XML representations with optional custom translators. Blocks can be categorized, collected into groups, and organized for selection in content management interfaces.

Use this package when you need a structured approach to managing composable content blocks with versioning, data integrity verification (via hashing), and flexible serialization formats.

### 1.2 Non-Goals

Nightfire does **not**:

- Provide a complete CMS or content management system—it focuses on the block composition and serialization layer.
- Handle HTTP request/response cycles or routing—it operates on data structures.
- Include built-in template engines or view renderers—blocks return `Tagged\Markup` but do not prescribe rendering strategies.
- Manage persistence or storage—it handles serialization/deserialization but not where data is stored.
- Provide authentication or authorization—security concerns are handled by consuming applications.

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `content` (see Chorus taxonomy)
- Nightfire sits in the content cluster as a mid-level package that provides the foundation for content block management. It depends on several core packages (`archetype`, `exceptional`, `exemplar`) and integrates with frontend packages (`tagged` for markup). It is typically used by higher-level content management or CMS packages that need structured block composition.

### 2.2 Typical Usage Contexts

Typical places Nightfire appears:

- Content management interfaces where blocks are selected, configured, and arranged.
- Content serialization/deserialization pipelines (JSON or XML).
- Form rendering systems that allow editing of block properties.
- Content rendering pipelines that convert block data to markup.

Nightfire is intended to be used whenever you need to manage composable content blocks with versioning, integrity checking, and flexible organization (categories and collections).

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Nightfire`
  The main service class that provides block and area inflation/deflation, category and collection loading, and block group building. Implements `Kingdom\Service` for dependency injection.

- `DecodeLabs\Nightfire\Block`
  Interface that all block implementations must satisfy. Defines serialization (`__serialize`, `__unserialize`), export to `BlockData`, rendering to `Tagged\Markup`, and metadata (type name, version, categories, collections).

- `DecodeLabs\Nightfire\Area`
  Container class that holds an ordered list of blocks. Implements `DataInterchange` for serialization to `AreaData`. Supports JSON and XML export.

- `DecodeLabs\Nightfire\BlockGroup`
  Groups blocks by category or collection. Provides sorted access to blocks and implements `Nuance\Dumpable` for debugging.

- `DecodeLabs\Nightfire\Category`
  Interface for categorizing blocks. Implementations define metadata (id, name, weight) and can filter which blocks they accept.

- `DecodeLabs\Nightfire\Collection`
  Interface for collecting blocks into named groups. Similar to `Category` but used for different organizational purposes.

- `DecodeLabs\Nightfire\BlockReference`
  Lightweight reference to a block class that provides metadata (type, weight, version, categories, collections) without instantiating the block.

- `DecodeLabs\Nightfire\Data\Block` / `DecodeLabs\Nightfire\Data\Area`
  Data transfer objects that represent serialized block and area data. Include type, version, hash, and payload data. Support compact JSON serialization (e.g., `b`, `v`, `h`, `d` keys).

- `DecodeLabs\Nightfire\DataInterchange`
  Interface for types that can be serialized to `Data` objects and exported as JSON or XML.

- `DecodeLabs\Nightfire\FormComponent` / `DecodeLabs\Nightfire\FormComponentFactory`
  Interfaces for creating form components that allow editing of block properties. `FormComponent` renders a form and applies changes to return a `Block`.

- `DecodeLabs\Nightfire\Strategy`
  Interface for block selection strategies that define constraints (e.g., `maxBlocks`).

- `DecodeLabs\Nightfire\Block\XmlTranslator` / `DecodeLabs\Nightfire\Area\XmlTranslator`
  Interfaces for custom XML serialization/deserialization of blocks and areas.

### 3.2 Main Entry Points

The main usage pattern is through the `Nightfire` service class:

```php
$nightfire = new Nightfire($archetype);

// Inflate a block from JSON, XML, or array
$block = $nightfire->inflateBlock($blockData);

// Inflate an area containing multiple blocks
$area = $nightfire->inflateArea($areaData);

// Export to JSON or XML
$json = $area->deflateToJson();
$xml = $area->deflateToXml();

// Organize blocks by category
$categories = $nightfire->loadAllCategories();
$blockGroups = $nightfire->buildBlockSelectionList($blocks);
```

Key concepts:

- **Blocks** are the atomic units of content, implementing `Block` with serialization and rendering capabilities.
- **Areas** are containers that hold ordered lists of blocks.
- **Categories** and **Collections** provide organizational taxonomies for blocks.
- **BlockGroups** organize blocks by category or collection for selection interfaces.
- **Data integrity** is maintained via hash verification on `BlockData` and `AreaData` objects.

---

## 4. Dependencies

### 4.1 Direct Decode Labs Dependencies

From `composer.json`:

- `decodelabs/archetype`
  Used for class discovery and resolution. `Nightfire` uses `Archetype` to scan for `Block`, `Category`, and `Collection` implementations and resolve block classes by type name.

- `decodelabs/coercion`
  Used for type coercion when inflating data from arrays or JSON. Ensures data types match expected structures.

- `decodelabs/exceptional`
  Used for exception handling. All exceptions thrown by Nightfire use the `Exceptional` pattern.

- `decodelabs/exemplar`
  Used for XML parsing and generation. `Element` is used for XML input, and `Writer` is used for XML output.

- `decodelabs/kingdom`
  Used for dependency injection. `Nightfire` implements `Service` and uses `ServiceTrait`.

- `decodelabs/nuance`
  Used for debugging and introspection. `BlockGroup`, `BlockReference`, `Category`, and `Collection` implement `Dumpable` for enhanced debugging output.

- `decodelabs/slingshot`
  Used internally for array utilities (e.g., `array_first`).

- `decodelabs/tagged`
  Used for markup representation. `Block::render()` returns `Tagged\Markup`.

### 4.2 External Dependencies

- `symfony/polyfill-php85`
  Provides PHP 8.5 features for compatibility.

See `composer.json` for supported PHP versions (PHP 8.4+).

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Block data hash verification: When inflating blocks or areas from external data (not already `BlockData`/`AreaData`), the hash is verified. If verification fails, an `UnexpectedValue` exception is thrown.
- Block type resolution: `inflateBlock()` and `translateXmlToBlockData()` require that the block type resolves to a class via `Archetype`. If resolution fails, an `UnexpectedValue` exception is thrown.
- Data serialization: All `DataInterchange` implementations must be able to export to their corresponding `Data` type and serialize to JSON/XML.
- Block versioning: Blocks define a `Versions` constant array, and the active version is determined by `defineActiveVersion()` (defaults to the first version or `'initial'`).

### 5.2 Input & Output Contracts

**Block Inflation:**

- Accepts: `string` (JSON or XML), `array`, `Exemplar\Element`, or `BlockData`
- Returns: `Block` instance
- Preconditions: Block type must resolve to a class; hash must match (unless inflating from `BlockData`)

**Area Inflation:**

- Accepts: `string` (JSON or XML), `array`, `Exemplar\Element`, or `AreaData`
- Returns: `Area` instance
- Preconditions: Area data must be valid; hash must match (unless inflating from `AreaData`)

**Block Export:**

- `Block::export()` returns `BlockData` with type, version, data, and computed hash
- `Area::export()` returns `AreaData` with id and list of `BlockData` objects

**Serialization:**

- `deflateToJson()` returns a JSON string representation
- `deflateToXml()` returns an XML string representation, optionally using custom `XmlTranslator` implementations

**Category/Collection Loading:**

- `loadAllCategories()` / `loadAllCollections()` return arrays keyed by category/collection id, sorted by weight
- If multiple classes resolve to the same id, the one with the higher weight is kept

---

## 6. Error Handling

### 6.1 Exception Types

Nightfire throws `Exceptional` exceptions:

- `Exceptional::UnexpectedValue`: Thrown when:
  - Block data hash mismatch during inflation
  - Block class not found for a given type
  - Invalid block/area data format
  - Missing required data fields
  - JSON encoding/decoding failures
  - Area translator class not found

### 6.2 Error Strategy

Nightfire follows a fail-fast strategy: invalid data or missing dependencies result in exceptions rather than silent failures or default values. This ensures data integrity and makes debugging easier. All exceptions use the `decodelabs/exceptional` pattern for consistent error handling across the ecosystem.

---

## 7. Configuration & Extensibility

### 7.1 Configuration

No runtime configuration is required. Block, category, and collection classes are discovered via `Archetype` scanning. The `Nightfire` service requires an `Archetype` instance to be provided via constructor injection.

### 7.2 Extension Points

Nightfire supports extension via:

- **Custom Block Implementations**: Implement `Block` interface (typically using `BlockTrait`) to create new block types. Blocks define their type name, version, categories, collections, and serialization data.

- **Custom XML Translators**: Implement `Block\XmlTranslator` or `Area\XmlTranslator` to provide custom XML serialization/deserialization logic. Translators are discovered via `Archetype` resolution.

- **Category Implementations**: Implement `Category` interface (typically using `CategoryTrait`) to define organizational categories for blocks. Categories can filter which blocks they accept via `acceptsBlock()`.

- **Collection Implementations**: Implement `Collection` interface (typically using `CollectionTrait`) to define collections of blocks. Collections can filter blocks via `acceptsBlock()`.

- **Form Components**: Implement `FormComponent` and `FormComponentFactory` to provide editing interfaces for blocks.

- **Strategy Implementations**: Implement `Strategy` to define block selection constraints (e.g., maximum blocks per area).

---

## 8. Interactions with Other Packages

Nightfire is designed to be used by / uses other packages:

- **`decodelabs/archetype`**
  Core dependency for class discovery. Nightfire uses Archetype to scan for and resolve block, category, and collection classes.

- **`decodelabs/exemplar`**
  Used for XML parsing and generation. Blocks and areas can be inflated from `Element` objects and exported to XML via `Writer`.

- **`decodelabs/tagged`**
  Blocks render to `Tagged\Markup`, allowing integration with frontend rendering systems.

- **`decodelabs/kingdom`**
  Nightfire implements `Service` for dependency injection integration.

- **`decodelabs/nuance`**
  Provides debugging capabilities via `Dumpable` interface implementations.

Design assumptions:

- `Archetype` is available and configured to scan the appropriate namespaces for block, category, and collection classes.
- Blocks are typically defined in a namespace structure that `Archetype` can discover (e.g., `MyApp\Block\*`).
- XML and JSON data formats follow the expected structure (type, version, hash, data for blocks; id and blocks array for areas).

---

## 9. Usage Examples

### 9.1 Creating and Serializing a Block

```php
use DecodeLabs\Nightfire\Block;
use DecodeLabs\Nightfire\BlockTrait;

class MyBlock implements Block
{
    use BlockTrait;

    public const array Versions = ['1.0', '1.1'];
    public const array Categories = ['Content', 'Media'];

    public function __construct(
        public string $title,
        public string $content,
    ) {
    }

    public function __serialize(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->title = $data['title'];
        $this->content = $data['content'];
    }

    public function render(): ?Markup
    {
        return Markup::create('div', [
            Markup::create('h2', $this->title),
            Markup::create('p', $this->content),
        ]);
    }
}

// Create and export
$block = new MyBlock('Hello', 'World');
$json = $block->deflateToJson();
// {"b":"MyBlock","v":"1.0","h":"abc123...","d":{"title":"Hello","content":"World"}}
```

### 9.2 Inflating Blocks and Areas

```php
use DecodeLabs\Archetype;
use DecodeLabs\Nightfire;

$archetype = new Archetype();
$nightfire = new Nightfire($archetype);

// Inflate from JSON
$blockJson = '{"b":"MyBlock","v":"1.0","h":"abc123","d":{"title":"Hello","content":"World"}}';
$block = $nightfire->inflateBlock($blockJson);

// Inflate an area containing multiple blocks
$areaJson = '{"a":"main","bx":[{"b":"MyBlock","v":"1.0","h":"abc123","d":{...}}]}';
$area = $nightfire->inflateArea($areaJson);

// Export area to XML
$xml = $area->deflateToXml();
```

### 9.3 Organizing Blocks by Category

```php
// Load all available block references
$blockReferences = $nightfire->loadAllBlockReferences();

// Build selection list grouped by category
$blockGroups = $nightfire->buildBlockSelectionList($blockReferences);

// Access blocks by category
foreach ($blockGroups as $categoryId => $group) {
    echo $group->name . "\n";
    foreach ($group->blocks as $blockRef) {
        echo "  - {$blockRef->type}\n";
    }
}
```

### 9.4 Custom XML Translation

```php
use DecodeLabs\Exemplar\Element;
use DecodeLabs\Exemplar\Writer;
use DecodeLabs\Nightfire\Block;
use DecodeLabs\Nightfire\Block\XmlTranslator;

class CustomBlock implements Block, XmlTranslator
{
    use BlockTrait;

    public static function readXml(Element $element): array
    {
        // Custom XML parsing logic
        return [
            'title' => $element->getAttribute('title'),
            'content' => $element->getTextContent(),
        ];
    }

    public static function writeXml(Writer $writer, array $data): void
    {
        // Custom XML generation logic
        $writer->startElement('custom-block', [
            'title' => $data['title'],
        ]);
        $writer->setTextContent($data['content']);
        $writer->endElement();
    }
}
```

---

## 10. Implementation Notes (For Contributors)

### 10.1 Internal Architecture

At a high level, Nightfire:

- Uses `Archetype` for class discovery and resolution, allowing blocks, categories, and collections to be discovered automatically.
- Separates data representation (`BlockData`, `AreaData`) from domain objects (`Block`, `Area`) to support multiple serialization formats.
- Uses hash verification (XXH3) to ensure data integrity during inflation.
- Supports versioning at the block level, allowing migration and compatibility handling.
- Provides traits (`BlockTrait`, `CategoryTrait`, `CollectionTrait`, `DataTrait`) to reduce boilerplate in implementations.

Contributors should:

- Preserve the separation between data transfer objects (`Data` subclasses) and domain objects (`Block`, `Area`).
- Maintain hash verification for data integrity—do not skip hash checks without clear justification.
- Follow the `TypeNameProvider` pattern for consistent type name resolution across blocks, categories, and collections.
- Use `Archetype` for all class discovery—avoid hardcoding class names or using direct instantiation where discovery is possible.

### 10.2 Performance Considerations

- Block inflation uses `ReflectionClass::newInstanceWithoutConstructor()` and `__unserialize()` to avoid calling constructors, which may be expensive for complex blocks.
- Hash generation uses XXH3 for fast hashing of JSON-encoded data.
- Block group building sorts blocks lazily (only when `$blocks` property is accessed).
- Category/collection loading caches results per `Nightfire` instance (via `Archetype` scanning).

### 10.3 Gotchas & Historical Decisions

- **Compact JSON keys**: `BlockData` and `AreaData` use short keys (`b`, `v`, `h`, `d`, `a`, `bx`) in JSON serialization to reduce payload size. The `from()` methods accept both short and long key names for backward compatibility.
- **Hash verification**: Hash verification is skipped when inflating from `BlockData`/`AreaData` directly, as these objects are assumed to be trusted. Verification only occurs when inflating from external formats (JSON, XML, arrays).
- **Version handling**: The active version defaults to the first entry in `Versions` or `'initial'` if empty. This allows simple versioning without explicit version management.
- **Type name resolution**: Type names are derived from class names by default, but can be overridden via the `TypeName` constant. The resolution logic looks for `\Block\`, `\Category\`, or `\Collection\` namespace segments to extract short names.

---

## 11. Testing & Quality

### 11.1 Testing Strategy

Tests should cover:

- Block and area inflation from JSON, XML, and array formats
- Hash verification and mismatch handling
- Block export and serialization (JSON and XML)
- Category and collection loading and organization
- Block group building and sorting
- Custom XML translator implementations
- Edge cases: empty areas, blocks with no data, missing types

### 11.2 Quality Signals

From the Decode Labs package index (at time of writing):

- **Code:** Tracked centrally in Chorus
- **Readme:** Tracked centrally in Chorus
- **Docs:** Tracked centrally in Chorus
- **Tests:** Tracked centrally in Chorus

Nightfire is in active development (milestone m1) and is considered stable for the defined API surface.

---

## 12. Roadmap & Future Ideas

Non-binding ideas:

- Enhanced version migration support for blocks (automatic data transformation between versions)
- Block validation system (schema validation for block data)
- Block dependency tracking (blocks that require or are incompatible with other blocks)
- Enhanced form component system with field validation
- Block preview/thumbnail generation
- Performance optimizations for large areas with many blocks

---

## 13. References

- **Chorus docs:**
  - Architecture principles
  - Package taxonomy & clusters
  - Backwards compatibility strategy (once published)

- **Related packages:**
  - `decodelabs/archetype` (class discovery and resolution)
  - `decodelabs/exemplar` (XML parsing and generation)
  - `decodelabs/tagged` (markup representation)
  - `decodelabs/exceptional` (exception handling)

- **Repository:**
  - `https://github.com/decodelabs/nightfire`

---

> This spec is intended to stay in sync with the **actual behaviour** of the package.
> When you make significant changes to the public surface or semantics, please update this document and, where applicable, add or update ADRs in Chorus.

