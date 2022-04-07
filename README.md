# Concrete Console
![PHPUnit](https://github.com/concrete5/console/actions/workflows/phpunit.yml/badge.svg)
![PHPCS](https://github.com/concrete5/console/actions/workflows/phpcs.yml/badge.svg)
![Psalm](https://github.com/concrete5/console/actions/workflows/psalm.yml/badge.svg)


A command line utility for working with Concrete CMS.

## Installation

### As a PHAR file

The latest version of the console cli tool is available at the following address:

https://github.com/concrete5/console/releases/latest/download/concrete.phar

#### Installation on Posix Systems

You simply have to download it and make it executable:

```sh
curl -L -o /usr/local/bin/concrete https://github.com/concrete5/console/releases/latest/download/concrete.phar
chmod +x /usr/local/bin/concrete
```

#### Installation on Windows Systems

You can download the `concrete.phar` file in a directory listed in your `PATH` environment variable (for example: `C:\Windows\System32`),
and create a `concrete.bat` file in the same directory with the following contents:

```batch
@php "%~dpn0.phar" %*
```

### With composer

The concrete console cli tool can also be installed globally with composer

    composer global require concrete5/console
    
If you haven't already, make sure to add the global composer bin directory to your PATH.

    export PATH="$(composer global config bin-dir --absolute --quiet):$PATH"

Note: This command will update the `PATH` environment variable only for the current session. In order to make it persistent you can add the line

    export PATH="$(composer global config bin-dir --absolute --quiet):$PATH"
    
To the `$HOME/.profile` file (for the current user only), or to `/etc/profile` (for any user)


## Running Commands

You can run commands just like this

    concrete info
    
Which should get you something like:

    # Location
    Path to instance: /path/to/my/project/public
    # concrete5 Version
    Installed - Yes
    Core Version - 8.5.4
    Version Installed - 8.5.4
    Database Version - 20200609145307
    
If you want to run a command against a different site, or if you've installed the console utility globally, any command that operates against a particular Concrete instance also has an `--instance` option (or `-I` for short.)

    concrete info --instance=/path/to/my/site
    
Returns

    # Location
    Path to instance: /path/to/other/site/web
    # concrete5 Version
    Installed - Yes
    Core Version - 8.5.0
    Version Installed - 8.5.0
    Database Version - 20190301133300

## Roadmap

The most important items we want to currently focus on are:

* ~~Add the ability to dump sites, configurations, files and more into a standardized backup archive.~~
* ~~Add the ability to _restore_ a Concrete site from one of these standardized backup archives, by passing a file to a given `concrete restore my_backup.gz` command.~~
* Improved stability of backup and restore
* Restore into an uninstalled concrete5
* Backing up a version 6 site

After that, we'd be happy to add as many features as you'd like. Should this tool include the ability to create boilerplate block or package code? Absolutely! Should we move code sniffer and code fixing functionality from the core console command to this tool? Yes, please.

## FAQ

-

## Why is this tool not built into the core?

We wanted a unified, standardized place to offer a devops and developer's toolkit. The core didn't seem like a great place for it. We want to be able to iterate on this quickly, which means not tying releases of this console utility to releases of the core.

## Is this tool meant to replace the concrete5 utility that ships with the core?

I don't know yet. Perhaps in the long term, yes – but that seems like an awful lot of work. Let's just focus on making this tool augment and improve the tools _around_ Concrete CMS, and slowly sunset the original console utility that's built into the core.
