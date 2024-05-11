# WP Bundler

Build dependencies & Bundle production ready plugin.
<br>
WP Bundler is a CLI tool. It can configure and run build process, and bundle zip(s) for WordPress plugins.

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

require __DIR__ . "/vendor/codesvault/wp-bundler/autoload.php";

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

Now Let's add build pipeline in the above `bundler` file like below. Then from terminal run `php bundler` to create a production zip.


```php
// basic uses

$bundler
    ->createProductionRepo('kathamo')
    ->command("composer install --no-dev")
    ->command("npm install")
    ->command("npm run build")
    ->cleanUp()
    ->zip('kathamo');
```

<br>

It's creating a repo in the `/prod` folder then running build `command` then 'cleaning' up the repo based on `.distignore` and finally making a zip.

<br>
<br>

## Envirnoment variables

Get env file data using WP Bundler.

```php
$env = CodesVault\Bundle\Setup::loadEnv(__DIR__, '.env');

if ('true' === $env->getenv('DEV_MODE')) {
  $bundler
    ->command("composer install")
    ->command("npm install")
    ->command("npm run build");
}
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
$tiers_pids = $setup->mapTiersAndProductIds($setup->getEnv('TIERS_PID'));

$bundler
  ->createProductionRepo('kathamo')
  ->command("composer install --no-dev")
  ->command("npm install")
  ->command("npm run build")
  ->cleanUp()
  ->buildIterator($tiers_pids, function($meta, $builder) {
    $zip_name = "kathamo-" . ucfirst($meta['tier']) . "-" . $meta['product_id'];

    $builder
      ->command("composer install --no-dev")
      ->zip($zip_name);
  });
```
