# Installs the PHP Quality Assurance Toolchain.
#
# Requires:: https://github.com/experience/vagrant-puppet-php

class phpqatools {
  $bin_path = '/usr/bin/'

  php::pear::install { 'phpqatools::install_php_codesniffer':
    package      => 'PHP_CodeSniffer',
    creates      => "${phpqatools::bin_path}phpcs",
    dependencies => 'true',
  }

  php::pear::install { 'phpqatools::install_php_copypastedetector':
    package      => 'pear.phpunit.de/phpcpd',
    creates      => "${phpqatools::bin_path}phpcpd",
    dependencies => 'true',
  }

  php::pear::install { 'phpqatools::install_php_depend':
    package      => 'pear.pdepend.org/PHP_Depend-beta',
    creates      => "${phpqatools::bin_path}pdepend",
    dependencies => 'true',
  }

  php::pear::install { 'phpqatools::install_php_loc':
    package      => 'pear.phpunit.de/phploc',
    creates      => "${phpqatools::bin_path}phploc",
    dependencies => 'true',
  }

  php::pear::install { 'phpqatools::install_php_mess_detector':
    package      => 'pear.phpmd.org/PHP_PMD',
    creates      => "${phpqatools::bin_path}phpmd",
    dependencies => 'true',
  }

  php::pear::install { 'phpqatools::install_phpunit':
    package      => 'pear.phpunit.de/PHPUnit',
    creates      => "${phpqatools::bin_path}phpunit",
    dependencies => 'true',
  }
}

