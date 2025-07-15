---
pygments_highlighter:
  on_exception: warn
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
