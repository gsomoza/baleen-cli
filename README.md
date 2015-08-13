# Baleen CLI
[![Build Status](https://travis-ci.org/baleen/cli.svg?branch=master)](https://travis-ci.org/baleen/cli)
[![Code Coverage](https://scrutinizer-ci.com/g/baleen/cli/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/baleen/cli/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/baleen/cli/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/baleen/cli/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/baleen/cli.svg)](https://packagist.org/packages/baleen/cli)

[![Author](http://img.shields.io/badge/author-@gabriel_somoza-blue.svg)](https://twitter.com/gabriel_somoza)
[![License](https://img.shields.io/packagist/l/baleen/cli.svg)](https://github.com/baleen/cli/blob/master/LICENSE)

Generic, customisable command-line wrapper for Baleen Migrations.

## Installation

With Composer:

```bash
composer install baleen/cli
```

Baleen CLI is quite opinionated in its defaults, so it doesn't need extra configuration to run. So if you'd like to just
 test-drive the project, you can now jump straight to the "usage" section.

But you can customize almost anything through a configuration file. To create a configuration file, run the following:

```bash
./vendor/bin/baleen init
```

This will generate two files in your working directory:  

* `.baleen.yml`: the configuration file.
* `.baleen_versions`: a simple file to keep track of which versions have been migrated. This can later be replaced
 with a database table. You may want to ignore this file in your VCS system (e.g. using Git's `.gitignore` file).  
 
The `.baleen_versions` file will be created for you automatically if you use the default configuration values. You 
don't need to run `baleen init` in order for the file to be created.
 
If you don't want to type `./vendor/bin/baleen` to run baleen commands then you can alternatively use Composer as a
shortcut. Just edit your project's `composer.json` file to add the following:
 
 ```json
 {
    "scripts": {
        "baleen": "vendor/bin/baleen --ansi"
    }
 }
 ```
 
Now you can run Baleen CLI easily by just typing `composer baleen`!

## Usage

To see some help and a list of available commands, simply execute:

```bash
./vendor/bin/baleen
```

For more help on a specific command simply run `./vendor/bin/baleen help {command}`, replacing `{command}` with the name
of an available command.

### Creating Migrations

Migrations are stored by default under the `./migrations` directory, which will be automatically created every time
your run a commend if it doesn't exist.

You can customise which directory to use for migrations, as well as the namespace for migration classes by editing the 
`.baleen.yml` config file.

To easily create a new Migration file run the following command:

```bash
./vendor/bin/baleen migrations:create
```

### Running Migrations

Running migrations is as easy as executing:

```bash
./vendor/bin/baleen migrate
```

By default it will migrate `up` to the latest available version.

If you'd like to see a log instead of the progress-bar then just add the `--no-progress` option to the migrate command above.

## License
Licensed under MIT - for more details please refer to the [LICENSE](https://github.com/baleen/cli/blob/master/LICENSE) 
file at the root directory.
