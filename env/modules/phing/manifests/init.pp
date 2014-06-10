# Installs Phing.
#
# Requires:: https://github.com/experience/vagrant-puppet-php

class phing{
  $bin_path = '/usr/bin/'

  exec{ 'phing::discover_channel': 
    command => 'sudo pear channel-discover pear.phing.info',
    onlyif  => 'test ! -f /usr/bin/phing',
  }

  php::pear::install { 'phing::install_phing':
    package => 'phing/phing',
    creates => '/usr/bin/phing',
    dependencies => 'true',
    require => Exec['phing::discover_channel'],
  }
}

