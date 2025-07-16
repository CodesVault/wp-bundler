# WP Bundler

Build dependencies & Bundle production ready plugin.
<br>
WP Bundler is a CI/CD tool. It can configure and run build process, and bundle zip(s) for WordPress plugins.

<br>

## Requirements
- Environment: Mac, Linux.
- PHP CLI 7.4 >=
- Composer

<br>
<br>

## Installation


It is required to use composer to install WP Bundler.
<br>

```bash
composer require codesvault/wp-bundler --dev
```

<br>


## Setup

Create a `bundler` file in the root folder of your plugin. E.g. `wp-content/plugins/kathamo/bundler`.
Add the below code in the file.

```php
#!/usr/bin/env php
<?php

use CodesVault\Bundle\Bundler;
use CodesVault\Bundle\Setup;

require __DIR__ . "/vendor/autoload.php";

$bundler = new Bundler(__DIR__);
```

<br>

Make a `.distignore` file in the root folder of your plugin. Add all those files, folders which you want to exclude from the production zip like below.

```
bundler

node_modules
package.json
package-lock.json

composer.json
composer.lock

assets/dev
```

<br>
<br>

## Uses

```php
// basic uses

$bundler
    ->createProductionRepo('kathamo')
    ->command("composer install --no-dev")
    ->command("npm install")
    ->command("npm run build")
    ->cleanUp()
    ->zip('kathamo')
    ->executionTime();
```

Now Let's add build pipeline in the above `bundler` file using this codes. Then from terminal `cd` into plugin's folder and run the below command to create a production zip.

```bash
php bundler
```

<br>

It's creating a repo in the `/pluginName/prod` folder then running build `command` then 'cleaning' up the repo based on `.distignore` and finally making a zip.

<br>
<br>

## Envirnoment variables

Get env file data using WP Bundler.

```php

// .env file
DEV_MODE='true'
TIERS_PRODUCTIDS="basic:1722795,plus:1722797,elite:1722799"


// bundler file
$env = CodesVault\Bundle\Setup::loadEnv(__DIR__, '.env');

if ('true' === $env->getenv('DEV_MODE')) {
  $bundler
    ->command("composer install")
    ->command("npm install")
    ->command("npm run build");
}

$tiers_pids = $setup->kv($setup->getEnv('TIERS_PID'));
// array (
//   [
//     'key'   => 'basic',
//     'value' => '1722795',
//   ],
//   [
//     'key'   => 'plus',
//     'value' => '1722797',
//   ],
//   [
//     'key'   => 'elite',
//     'value' => '1722799',
//   ],
// );
```

<br>
<br>

## Update File content

You can also update specific file data dynamically before making the zip using `updateFileContent` api.

### Configuration
Create a `bundler-schema.json` file in the root folder of your plugin.
`bundler-schema.json` file data structure will be like below.

```json
{
  "kathamo": {
    "path": "",
    "extension": "php",
    "schema": [
      {
        "target": "Plugin Name: Kathamo",
        "template": "Plugin Name: Kathamo {{tier_name}}"
      },
      {
        "target": "Version: 1.5.2",
        "template": "Version: {{release_version}}"
      },
      {
          "target": "define('CV_VERSION', '1.5.2');",
          "template": "define('CV_VERSION', '{{release_version}}');"
      }
    ]
  },
  "README": {
    "path": "",
    "extension": "txt",
    "schema": [
      {
        "target": "Version: 1.5.2",
        "template": "Version: {{release_version}}"
      }
    ]
  }
}
```

Here `Kathamo, README` these keys are the file names. `path` is the file's relative path. `extension` is the file extension. `schema` is the array of objects where `target` is the data which you want to update and `template` is the data which you want to replace with.

<br>

### Usage

```php
// bundler file

// bundler-schema.json file's `{{placeholder}}` name should be same as the key names in the below array.
$intended_data = [
  "tier_name"       => "Pro",
  "release_version" => $setup->getEnv('RELEASE_VERSION'),
];

$bundler
  ->createProductionRepo('kathamo')
  ->command("composer install --no-dev")
  ->cleanUp()
  ->updateFileContent($intended_data)
  ->zip('kathamo');
```

<br>
<br>

## Find and Replace

Update entire plugin file's data using `findAndReplace` api.

```php
$bundler
  ->createProductionRepo('kathamo')
  ->findAndReplace([
    [
      'find'          => "use Kathamo\\Bundle\\Bundler;",
      'updated_data'  => "use CodesVault\\Kathamo\\Bundle\\Bundler;",
    ]
  ])
  ->cleanUp()
  ->zip($zip_name);
```

<br>
<br>

## Multiple Zips

When you want to create multiple zip, use `buildIterator` api.

```php
// .env file
TIERS_PID="basic:123,plus:231,pro:3240"


// bundler file
$setup = Setup::loadEnv(__DIR__, '.env');
$tiers_pids = $setup->kv($setup->getEnv('TIERS_PID'));

$bundler
  ->createProductionRepo('kathamo')
  ->command("composer install --no-dev")
  ->command("npm install")
  ->command("npm run build")
  ->cleanUp()
  ->buildIterator($tiers_pids, function($meta, $builder) {
    $zip_name = "kathamo-" . $meta['key'] . "-" . $meta['value'];

    $builder
      ->zip($zip_name);
  })
  ->executionTime();
```

<br>
<br>

## Callable Function as command

You can also use callable or callback function as command. It will be executed in the prod repo folder.

```php
$bundler
  ->createProductionRepo('kathamo')
  ->command('foo')
  ->command(function() {
    echo "Hello world!\n";
    return foo();
  })
  ->cleanUp()
  ->buildIterator($tiers_pids, function($meta, $builder) {
    $zip_name = "kathamo-" . $meta['key'] . "-" . $meta['value'];

    $builder
      ->zip($zip_name);
  })
  ->executionTime();


function foo() {
  // Do something here
}

```

<br>
<br>

## Example

Here is an example of a `bundler` file.

```php
#!/usr/bin/env php
<?php

require __DIR__ . "/vendor/autoload.php";

// data loaded from .env file
$setup = Setup::loadEnv(__DIR__, '.env');
$tiers_pids = $setup->kv($setup->getEnv('TIERS_PID'));

$bundler
  ->createProductionRepo('kathamo')
  ->command("composer install --no-dev")
  ->command("npm install")
  ->command("npm run build")
  ->cleanUp()
  ->copy('/schema.json', '/schema.json')
  ->renameProdFile('kathamo.php', 'kathamo-pro.php')
  ->buildIterator($tiers_pids, function($meta, $builder) {
    $zip_name = "kathamo-" . $meta['key'] . "-" . $meta['value'];
    $intended_data = [
      "tier_name"       => $meta['tier'],
      "release_version" => $setup->getEnv('RELEASE_VERSION'),
    ];

    $builder
      ->updateFileContent($intended_data)
      ->findAndReplace([
        [
          'find'          => "use Kathamo\\Bundle\\Bundler;",
          'updated_data'  => "use CodesVault\\Kathamo\\Bundle\\Bundler;",
        ]
      ])
      ->zip($zip_name);
  })
  ->executionTime();
```
