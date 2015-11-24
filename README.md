# Composer preserve paths

Composer plugin for remove paths while installing or updating packages.

## Installation

Simply install the plugin with composer: `composer require ruuds/composer-preserve-paths`

## Configuration

For configuring the paths you need to set `remove-paths` within the `extra` of your root `composer.json`.

```json
{
    "extra": {
        "remove-paths": [
          "htdocs/robots.txt/all/modules/contrib"
        ]
      }
}
```