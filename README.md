# Stempler
[![Latest Stable Version](https://poser.pugx.org/spiral/stempler/version)](https://packagist.org/packages/spiral/stempler)
[![Build Status](https://travis-ci.org/spiral/stempler.svg?branch=master)](https://travis-ci.org/spiral/stempler)
[![Codecov](https://codecov.io/gh/spiral/stempler/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/stempler/)

Template engine framework with layered multi-grammar support and AST processing. Supports compile-time template composition,
inheritance, context aware escaping, blade directives. Debug tools included with source-map support.

## Example
```php
<extends:layouts.parent title="My Page"/>
<use:element path="path/element"/>

<block:content>
    <element label="hello world">{{ $variable }}</element>
</block:content>
```
