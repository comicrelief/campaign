# aGov [![Build Status](https://travis-ci.org/previousnext/agov.svg?branch=2.x)](https://travis-ci.org/previousnext/agov)

## Download

aGov is available as a full drupal site in tgz and zip format at: http://drupal.org/project/agov

## Building from Source

Source is available from GitHub at https://github.com/previousnext/agov

## Requirements

* Vagrant 1.6+ (+ Plugins) - http://docs.vagrantup.com/v2/installation
* Virtualbox - https://www.virtualbox.org/wiki/Downloads

**Install Vagrant plugins**

Run the following via the command line:

```bash
# Virtualbox support.
$ vagrant plugin install vagrant-vbguest

# Automatically assigns an IP address.
$ vagrant plugin install vagrant-auto_network

# Adds "/etc/hosts" (local DNS) records.
$ vagrant plugin install vagrant-hostsupdater
```

## Getting started

**1) Start the VM.**

```bash
$ vagrant up
```

All commands from here are to be run within the VM. This can be done via the command:

```bash
$ vagrant ssh
```

This will take you to the root of the project **inside** of the vm.

**2) Pull down the dependencies**

```bash
$ composer install --prefer-dist
```

**3) Build the project**

```bash
$ phing
```

The default build task is to build the project. To call this step directly, run:

**3) Go to the site on the following domain**

```
http://agov.dev
```

```bash
$phing build
```

## Testing

```bash
$ phing test
```

## List other targets

```bash
$ phing -l
```

The output for this should look something like the following:

```
Default target:
-------------------------------------------------------------------------------
 build            Build (or rebuild) the project.

Main targets:
-------------------------------------------------------------------------------
 behat            Run Behat tests and print human readable output. Intended for usage on the command line before committing.
 behat:init       Setup steps for Behat build tasks.
 build            Build (or rebuild) the project.
 ci:behat         Run Behat tests creating a log file for the continuous integration server.
 ci:phpcs         Find coding standard violations using PHP_CodeSniffer creating a log file for the continuous integration server.
 ci:phpmd         Perform project mess detection using PHPMD creating a log file for the continuous integration server
 install          Install aGov with standard configuration.
 make             Compile aGov from a make file
 phpcpd           Find duplicate code using PHPCPD
 phpcs            Find coding standard violations using PHP_CodeSniffer and print human readable output. Intended for usage on the command line before committing.
 phploc           Measure project size using PHPLOC
 phpmd            Perform project mess detection using PHPMD and print human readable output. Intended for usage on the command line before committing.
 phpqatools:init  Setup steps for PHP build tasks.
 prepare          Setup the project
 test             Run the test suite
```
