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

Create a `bundler` file in the root folder of your plugin. Add the below code in the file.

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

You can also update specific file data before making zip using `updateFileContent` api.

<!-- TODO: add doc -->

<br>

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

## Multiple Zip

When you want to create multiple zip, use `buildIterator` api.

```php
$production_repos = [
  "feed-pro-basic",
  "feed-pro-plus",
];

$bundler
  ->buildIterator($production_repos, function($repo_name, $builder) {
    $builder
      ->createProductionRepo($repo_name)
      ->command("composer install --no-dev")
      ... ..
  });
```
