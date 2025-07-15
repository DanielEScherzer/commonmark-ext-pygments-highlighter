# CommonMark Extension Pygments Highlighter

The `PygmentsHighlighter` extension adds support for using pygments to style
blocks of code in Markdown documents.

## Installation

This extension can be installed from packagist via composer:

```bash
composer require danielescherzer/commonmark-ext-pygments-highlighter
```

It depends on

* `league/commonmark` (version 2.7 or above; all lower versions have security
issues)
* `ramsey/pygments` (version 3.0 or above)

Separately (not enforced via composer) you need to have python and the pygments
python library installed. The extension is currently tested against

* Python 3.11, 3.12, and 3.13
* Pygments 2.17, 2.18, and 2.19

The pygments command is assumed to be at `pygmentize` (in the PATH); if this is
not the case configure the correct path in the extension setup, see the
configuration section below.

## Syntax and results

Sample Markdown input:

````markdown
```php
<?php
class User {
	private int $id;
	private string $name;

	public function __construct( int $id, string $name ) {
		$this->id = $id;
		$this->name = $name;
	}
}
```
````

Result:

```html
<div class="pygments-highlighter"><div class="highlight"><pre><span></span><span class="cp">&lt;?php</span>
<span class="k">class</span> <span class="nc">User</span> <span class="p">{</span>
	<span class="k">private</span> <span class="nx">int</span> <span class="nv">$id</span><span class="p">;</span>
	<span class="k">private</span> <span class="nx">string</span> <span class="nv">$name</span><span class="p">;</span>

	<span class="k">public</span> <span class="k">function</span> <span class="fm">__construct</span><span class="p">(</span> <span class="nx">int</span> <span class="nv">$id</span><span class="p">,</span> <span class="nx">string</span> <span class="nv">$name</span> <span class="p">)</span> <span class="p">{</span>
		<span class="nv">$this</span><span class="o">-&gt;</span><span class="na">id</span> <span class="o">=</span> <span class="nv">$id</span><span class="p">;</span>
		<span class="nv">$this</span><span class="o">-&gt;</span><span class="na">name</span> <span class="o">=</span> <span class="nv">$name</span><span class="p">;</span>
	<span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
```

## Usage

Configure your `Environment` as usual and simply add an instance of the
`PygmentsHighlighterExtension`:

```php
<?php

use DanielEScherzer\CommonMarkPygmentsHighlighter\PygmentsHighlighterExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

// These are the defaults for the extension, you can change them
$config = [
    'pygments_highlighter' => [
        'pygmentize_path' => null, // Use `pygmentize` from PATH
        'on_exception' => 'warn',
    ],
];

// Configure the Environment with the desired extensions
$environment = new Environment( $config );
$environment->addExtension( new CommonMarkCoreExtension() );
$environment->addExtension( new PygmentsHighlighterExtension() );

// And convert your markdown
$converter = new MarkdownConverter($environment);
echo $converter->convert("```php\n<?php\necho 'testing...';\n```");
```

## Configuration

This extension can be configured by providing a `pygments_highlighter` array
with the following options (defaults shown above):

### `pygmentize_path`

The path to the pygmentize command, or null to use the default (`pygmentize`).

### `on_exception`

If the attempt to use the pygmentize command fails, this value controls the
behavior of the rendererer. By default, it is set to 'warn' - it can be:

* 'warn' - show a warning and render the code with the CommonMark-provided
default output
* 'ignore' - render the code with the CommonMark-provided default output
* 'throw' - rethrow the exception with the failure details

## Adding styles

This extension just adds the various classes for the output to be made
colorful, you still need to add CSS targetting the code. The `ramsey/pygments`
library offers a method to get an entire stylesheet for any of the pygments
built-in styles, or you can build your own styling.

All of the highlighted code output from this extension will be within a
div with the `pygments-highlighter` class, so your CSS rules can be scoped to
only elements within the `.pygments-highlighter` selector.
