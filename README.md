# Tag Colors

Assign a hex color to taxonomy terms via the admin UI. Values are stored as **term meta** (`tag_color`), including REST API support.

## Requirements

- WordPress 5.8+
- PHP 7.4+

## Composer

Install into a WordPress project (requires [composer/installers](https://github.com/composer/installers) in the root `composer.json`):

```json
{
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/Horttcore/tag-colors"
		}
	],
	"require": {
		"horttcore/tag-colors": "^1.0"
	},
	"extra": {
		"installer-paths": {
			"wp-content/plugins/{$name}/": ["type:wordpress-plugin"]
		}
	}
}
```

Development dependencies (PHPCS + WordPress coding standards):

```bash
composer install
composer phpcs
```

## Setup

Activate the plugin, then register each taxonomy on `init`:

```php
add_action( 'init', function () {
	Tag_Colors::register_support( 'post_tag' );
} );
```

## Reading colors

```php
$hex = tag_colors_get_color( $term );
// or
$hex = tag_colors_get_color( $term_id );
```

## Changelog

### 1.0.0

- Term meta storage, REST registration, security hardening
- `tag_colors_get_color()` helper

### 0.2

- Clear the color field to remove a color
- No default `post_tag` registration (opt-in only)

### 0.1

- Initial release
