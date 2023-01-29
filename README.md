# Yii2 Widgets 2 Addons

This extension adds some useful functionality to the [Yii2 Widgets 2](https://github.com/dmstr/yii2-widgets2-module) module.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require eluhr/yii2-widgets2-addons
```

or add

```
"eluhr/yii2-widgets2-addons": "*"
```

to the required section of your `composer.json` file.

## Configuration

Add the following to your `config/web.php`:

```php

use eluhr\widgets2\addons\Module;
[
    'modules' => [
        'widgets-addons' => [
            'class' => Module::class
        ]
    ]
]
```

## Usage

Add the Widget to your view:

```php
use eluhr\widgets2\addons\widgets\CellLiveEditor;

echo CellLiveEditor::widget();
```
