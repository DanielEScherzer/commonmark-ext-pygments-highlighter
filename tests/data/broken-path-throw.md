---
pygments_highlighter:
  pygmentize_path: broken
  on_exception: throw
except_exception: "An error occurred while running pygmentize: sh: 1: exec: broken: not found"
---

# Testing

Before

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

after
