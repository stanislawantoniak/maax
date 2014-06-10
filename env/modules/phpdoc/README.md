# Vagrant Puppet phpDocumentor
Puppet manifest to install [phpDocumentor 2][phpdoc] on our default Ubuntu Precise Vagrant development server.

[phpdoc]: http://www.phpdoc.org/

## Requirements
The PHP QA Tools module requires our [Vagrant Puppet PHP module][php_module], in order to install the aforementioned PEAR packages.

[php_module]: https://github.com/experience/vagrant-puppet-php

## Usage
To install phpDocumentor 2 add one of the following to your manifest:

- `class { 'phpdoc': }`
- `include 'phpdoc'`
