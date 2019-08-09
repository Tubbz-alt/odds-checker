# The-odds-api Client [![Build Status](https://travis-ci.com/he110/odds-checker.svg?branch=master)](https://travis-ci.com/he110/odds-checker)

[![Total Downloads](https://img.shields.io/packagist/dt/he110/odds-checker.svg)](https://packagist.org/packages/he110/odds-checker)
[![Latest Stable Version](https://img.shields.io/packagist/v/he110/odds-checker.svg)](https://packagist.org/packages/he110/odds-checker)

Tools for parsing, caching and using ODDS from https://the-odds-api.com

## Installation

Install the latest version with

```bash
$ composer require he110/odds-checker
```

## Basic Usage

```php
<?php

use He110\OddsChecker\Checker;

$checker = new Checker(PASTE_YOUR_API_KEY_HERE);

//To get upcoming, use short version
$checker->getData();

//or user sport-specified version
$checker->getData("soccer_epl");

//you can also specify region code
$checker->getData("soccer_epl", "uk");

```

## About

### Requirements

- OddsChecker works with PHP 7.2 or above.

### Submitting bugs and feature requests

Bugs and feature request are tracked on [GitHub](https://github.com/he110/odds-checker/issues)

### Author

Ilya S. Zobenko - <ilya@zobenko.ru> - <http://twitter.com/he110_todd>

### License

OddsChecker is licensed under the MIT License - see the `LICENSE` file for detail
