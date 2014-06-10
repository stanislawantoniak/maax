# Installs phpDocumentor 2.
#
# Requires:: https://github.com/experience/vagrant-puppet-php

class phpdoc {
  $bin_path = '/usr/bin/'

  php::extension { 'php5-xsl': }

  php::pear::discover { 'pear.phpdoc.org': }

  php::pear::install { 'phpdoc::install_phpdoc':
    package => 'pear.phpdoc.org/phpDocumentor-alpha',
    creates => '/usr/bin/phpdoc',
    require => Php::Pear::Discover['pear.phpdoc.org'],
  }
}

