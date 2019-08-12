# The-odds-api Client [![Build Status](https://travis-ci.com/he110/odds-checker.svg?branch=master)](https://travis-ci.com/he110/odds-checker)

[![Latest Stable Version](https://img.shields.io/packagist/v/he110/odds-checker.svg)](https://packagist.org/packages/he110/odds-checker) [![codecov](https://codecov.io/gh/he110/odds-checker/branch/master/graph/badge.svg)](https://codecov.io/gh/he110/odds-checker) [![Maintainability](https://api.codeclimate.com/v1/badges/818933b27c2e569a7568/maintainability)](https://codeclimate.com/github/he110/odds-checker/maintainability)

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
$data = $checker->getData();

//or user sport-specified version
$data = $checker->getData("soccer_epl");

//you can also specify region code
$data = $checker->getData("soccer_epl", "uk");

//If you need to get team-specified data, use 'filterData' method
$filtered = $checker->filterData($data, "Liverpool");

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
