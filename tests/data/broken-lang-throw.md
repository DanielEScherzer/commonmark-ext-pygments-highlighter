---
pygments_highlighter:
  on_exception: throw
except_exception: "An error occurred while running pygmentize: Error: no lexer for alias 'broken' found"
---

# Testing

Before

```broken
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
