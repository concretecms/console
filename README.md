# Concrete Console

A command line utility for working with Concrete CMS.

## Installation

The Concrete console is most effectively installed using Composer.

    compose require concrete5/console
    
You can do this from within a composer-installed Concrete site, and the console utility will be immediately available. Just run

    vendor/bin/concrete
    
From within your project's composer root.

### Global Installation

You should also be able to install this globally with composer. 
   
    compose global require concrete5/console
    export PATH=~/.composer/vendor/bin:$PATH
    
## Running Commands

If you've installed the console utility in your project, you can run commands just like this

    vendor/bin/concrete info
    
Which should get you something like:

    # Location
    Path to installation: /path/to/my/project/public
    # concrete5 Version
    Installed - Yes
    Core Version - 8.5.4
    Version Installed - 8.5.4
    Database Version - 20200609145307
    
If you want to run a command against a different site, or if you've installed the console utility globally, any command that operates against a particular Concrete installation also has an `--installation` option (or `-i` for short.)

    /path/to/my/global/console/concrete info --installation=/path/to/my/site
    
Returns

    # Location
    Path to installation: /path/to/other/site/web
    # concrete5 Version
    Installed - Yes
    Core Version - 8.5.0
    Version Installed - 8.5.0
    Database Version - 20190301133300

## Roadmap

The most importanat items we want to currently focus on are:

* Add the ability to dump sites, configurations, files and more into a standardized backup archive.
* Add the ability to _restore_ a Concrete site from one of these standardized backup archives, by passing a file to a given `vendor/bin/concrete restore my_backup.gz` command.

After that, we'd be happy to add as many features as you'd like. Should this tool include the ability to create boilerplate block or package code? Absolutely! Should we move code sniffer and code fixing functionality from the core console command to this tool? Yes, please.

## Why is this tool not built into the core?

We wanted a unified, standardized place to offer a devops and developer's toolkit. The core didn't seem like a great place for it. We want to be able to iterate on this quickly, which means not tying releases of this console utility to releases of the core.

## Is this tool meant to replace the concrete5 utility that ships with the core?

I don't know yet. Perhaps in the long term, yes – but that seems like an awful lot of work. Let's just focus on making this tool augment and improve the tools _around_ Concrete CMS, and slowly sunset the original console utility that's built into the core.
