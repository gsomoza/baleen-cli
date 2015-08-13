# Baleen CLI
[![Build Status](https://travis-ci.org/baleen/cli.svg?branch=master)](https://travis-ci.org/baleen/cli)
[![Code Coverage](https://scrutinizer-ci.com/g/baleen/cli/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/baleen/cli/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/baleen/cli/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/baleen/cli/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/baleen/cli.svg)](https://packagist.org/packages/baleen/cli)

[![Author](http://img.shields.io/badge/author-@gabriel_somoza-blue.svg)](https://twitter.com/gabriel_somoza)
[![License](https://img.shields.io/packagist/l/baleen/cli.svg)](https://github.com/baleen/cli/blob/master/LICENSE)

Generic, customisable command-line wrapper for Baleen Migrations.

### Installation

With Composer:

```bash
composer install baleen/cli
```

And then to initialise Baleen for your project:

```bash
./vendor/bin/baleen init
```

This will generate two files in your working directory: 
* `.baleen.yml`: the configuration file.
* `.baleen_versions`: a simple database to keep track of which versions have been migrated. This can later be replaced
 with a database table. You may want to ignore this file in your VCS system (e.g. using Git's `.gitignore` file).

### Usage

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

Running migrtaions is as easy as executing:

```bash
./vendor/bin/baleen migrate
# or alternatively
composer baleen migrate
```

If you'd like to see a log instead of the progress-bar then just add the `--no-progress` option to the migrate command above.

### License
MIT - for more details please refer to [LICENSE](https://github.com/baleen/cli/blob/master/LICENSE) at the root 
directory.
