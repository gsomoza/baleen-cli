# Baleen CLI

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

### License
MIT - for more details please refer to [LICENSE](https://github.com/baleen/cli/blob/master/LICENSE) at the root 
directory.
