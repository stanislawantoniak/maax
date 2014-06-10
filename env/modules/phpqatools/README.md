# Vagrant Puppet PHP QA Tools
Puppet manifest to install the [PHP Quality Assurance Toolchain][qatools] on our default Ubuntu Precise Vagrant development server.

The manifest will install the following, via PEAR:

- [PHP CodeSniffer][php_cs]
- [PHP Copy / Paste Detector][php_cpd]
- [PHP Depend][php_depend]
- [PHPLoc][phploc]
- [PHP Mess Detector][php_md]
- [PHPUnit][phpunit]

Note that it does not install [PHP CodeBrowser][php_cb].

[php_cb]: http://github.com/Mayflower/PHP_CodeBrowser
[php_cs]: http://pear.php.net/PHP_CodeSniffer
[php_cpd]: http://github.com/sebastianbergmann/phpcpd
[php_depend]: http://pdepend.org/
[phploc]: http://github.com/sebastianbergmann/phploc
[php_md]: http://phpmd.org/
[phpunit]: http://www.phpunit.de/
[qatools]: http://phpqatools.org/

## Requirements
The PHP QA Tools module requires our [Vagrant Puppet PHP module][php_module], in order to install the aforementioned PEAR packages.

[php_module]: https://github.com/experience/vagrant-puppet-php

## Usage
To install the PHP Quality Assurance Toolchain, add one of the following to your manifest:

- `class { 'phpqatools': }`
- `include 'phpqatools'`
