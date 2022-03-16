# Automated Values

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/ProfessionalWiki/AutomatedValues/CI)](https://github.com/ProfessionalWiki/AutomatedValues/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/ProfessionalWiki/AutomatedValues/branch/master/graph/badge.svg?token=GnOG3FF16Z)](https://codecov.io/gh/ProfessionalWiki/AutomatedValues)
[![Type Coverage](https://shepherd.dev/github/ProfessionalWiki/AutomatedValues/coverage.svg)](https://shepherd.dev/github/ProfessionalWiki/AutomatedValues)
[![Psalm level](https://shepherd.dev/github/ProfessionalWiki/AutomatedValues/level.svg)](psalm.xml)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/wikibase-automated-values/version.png)](https://packagist.org/packages/professional-wiki/wikibase-automated-values)
[![Download count](https://poser.pugx.org/professional-wiki/wikibase-automated-values/d/total.png)](https://packagist.org/packages/professional-wiki/wikibase-automated-values)
[![License](https://img.shields.io/packagist/l/professional-wiki/wikibase-automated-values)](LICENSE)

[Wikibase] extension that allows defining rules to automatically set labels or aliases based on Statement values.

Automated Values has been created and is maintained by [Professional.Wiki].

- [Usage](#usage)
- [Installation](#installation)
- [Configuration](#configuration)
- [Development](#development)
- [Release notes](#release-notes)

## Usage



## Installation

Platform requirements:

* [PHP] 7.4 or later (tested up to 8.1)
* [MediaWiki] 1.35 or later (tested up to 1.37)
* [Wikibase] 1.35 or later (tested up to 1.37)

The recommended way to install Automated Values is using [Composer] with
[MediaWiki's built-in support for Composer][Composer install].

On the commandline, go to your wikis root directory. Then run these two commands:

```shell script
COMPOSER=composer.local.json composer require --no-update professional-wiki/wikibase-automated-values:~1.0
```
```shell script
composer update professional-wiki/wikibase-automated-values --no-dev -o
```

Then enable the extension by adding the following to the bottom of your wikis [LocalSettings.php] file:

```php
wfLoadExtension( 'AutomatedValues' );
```

You can verify the extension was enabled successfully by opening your wikis Special:Version page in your browser.

## Configuration

Configuration can be changed via [LocalSettings.php].



## Development

To ensure the dev dependencies get installed, have this in your `composer.local.json`:

```json
{
	"require": {
		"vimeo/psalm": "^4",
		"phpstan/phpstan": "^1.4.9"
	},
	"extra": {
		"merge-plugin": {
			"include": [
				"extensions/AutomatedValues/composer.json"
			]
		}
	}
}
```

### Running tests and CI checks

You can use the `Makefile` by running make commands in the `` directory.

* `make ci`: Run everything
* `make test`: Run all tests
* `make cs`: Run all style checks and static analysis

Alternatively, you can execute commands from the MediaWiki root directory:

* PHPUnit: `php tests/phpunit/phpunit.php -c extensions/AutomatedValues/`
* Style checks: `vendor/bin/phpcs -p -s --standard=extensions/AutomatedValues/phpcs.xml`
* PHPStan: `vendor/bin/phpstan analyse --configuration=extensions/AutomatedValues/phpstan.neon --memory-limit=2G`
* Psalm: `php vendor/bin/psalm --config=extensions/AutomatedValues/psalm.xml`

### High level design

The `Domain/` folder contains the domain model, which is both independent of MediaWiki code and wiki concepts beyond the
Wikibase DataModel. In other words, the `Domain/` folder is the core of the application, with no outgoing dependencies.

`Hooks.php` acts as entry point and does object graph construction. Upon expansion of the extension, the later would go
into a dedicated top level factory.

## Release notes


### Version 1.0.0 - TBD

Initial release for Wikibase 1.35+ with these features:

* 

[Professional.Wiki]: https://professional.wiki
[Wikibase]: https://wikibase.consulting/what-is-wikibase/
[MediaWiki]: https://www.mediawiki.org
[PHP]: https://www.php.net
[Composer]: https://getcomposer.org
[Composer install]: https://professional.wiki/en/articles/installing-mediawiki-extensions-with-composer
[LocalSettings.php]: https://www.mediawiki.org/wiki/Manual:LocalSettings.php
