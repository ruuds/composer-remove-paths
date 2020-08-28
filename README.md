# Composer remove paths

This Composer plugin allows you to remove paths (files and/or directories) while
installing or updating packages, so you can remove unwanted files from your
project, eg. before deploying to production.

## Installation

Simply install the plugin with composer: `composer require ruuds/composer-remove-paths`

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