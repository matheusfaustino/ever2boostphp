# ever2BoostPHP

ever2BoostPHP is a CLI tool that convert Evernote's note to Boostnote, inspired by https://github.com/BoostIO/ever2boost, but using PHP.

# Installation

There are two ways of installing the package: [PHP Archive (PHAR)](https://secure.php.net/phar) and [Composer](https://getcomposer.org/). You'll need a machine with PHP >= 7.1 and that is it. 

## PHAR

It works like jar from java, and includes all required dependencies for ever2BoostPHP in a [single file](https://github.com/matheusfaustino/ever2boostphp/releases/download/1.0.2/ever2boostphp.phar):
```
$ wget https://github.com/matheusfaustino/ever2boostphp/releases/download/1.0.2/ever2boostphp.phar
$ php ever2boostphp.phar <Boostnote folder>
```

## Composer 

[Composer](https://getcomposer.org/doc/00-intro.md) is an application-level package manager for the PHP:
```
$ composer global require matheusfaustino/ever2boostphp
$ ever2boostphp <Boostnote folder>
```

# How it works

The Boostnote folder is the folder which has the `boostnote.json`, you'll find the place in the settings panel. 
The first thing that ever2boostPHP will ask you is which folder you would like to import the notes (eg: Evernote Migration), just select one of the folders and that is. Then, it'll ask for your authorization to download the notes. It'll download all the notes, convert it to Boostnote format and download all the resources (images, zip, etc) and link it to each note. 
It will show you a progress bar to keep track of the process. At the end, it'll show you the path where the notes are, so open it up and copy the folder `notes` and `attachments` inside of `output` and paste it in the Boostnote folder and restart the application.

# Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/matheusfaustino/ever2boostphp. 

### TODO
- [ ] Unit Test

### License
It is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
