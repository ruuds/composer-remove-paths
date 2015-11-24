# Composer preserve paths

Composer plugin to remove paths (files and/or directories) while installing or updating packages.

## Installation

Simply install the plugin with composer: `composer require ruuds/composer-preserve-paths`

## Configuration

For configuring the paths you need to set `remove-paths` within the `extra` of your root `composer.json`.

```json
{
    "extra": {
        "remove-paths": [
          "htdocs/robots.txt",
          "htdocs/directory-to-remove"
        ]
      }
}
```