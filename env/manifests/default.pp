import 'modules.pp'

$host = 'localhost'
$db_username = 'username'
$db_password = 'password'
$db_name     = 'modago'

$full_web_path = '/var/www'

Exec{path => '/bin:/usr/bin/:/usr/local/bin/'}

#  VIM
package{'vim':
  ensure => present,
}

# PYTHON SOFTWARE PROPERTIES
package{'python-software-properties':
  ensure => present,
}

# PHP

include php
include php::pear

class { ['php::fpm', 
         'php::cli', 
         'php::extension::curl',
         'php::extension::gd', 
         'php::extension::mcrypt', 
         'php::extension::mysql', 
         'php::extension::xdebug']: }

class { 'php::phpunit': }


# NGINX
class { 'nginx': }

nginx::resource::vhost { "$fqdn":
  www_root => '/var/www',
}

nginx::resource::location { "root":
  ensure          => present,
  ssl             => false,   
  ssl_only        => false,   
  vhost           => "$fqdn",
  www_root        => "${full_web_path}/",  
  location        => '~ \.php$',
  index_files     => ['index.php', 'index.html', 'index.htm'],
  proxy           => undef,
  fastcgi         => "127.0.0.1:9000",
  fastcgi_script  => undef,
  location_cfg_append => { 
    fastcgi_connect_timeout => '3m',
    fastcgi_read_timeout    => '3m',
    fastcgi_send_timeout    => '3m' 
  }
} 

file{'/var/www':
  ensure => link,
  target => '/vagrant',
  require => Class['nginx']
}

# MYSQL
class { 'mysql::server':
  config_hash => { 'root_password' => 'bartosz15' }
}
mysql::db { "$db_name":
  user => "$db_username",
  password => "$db_password",
  host => "$host",
  grant => ['all'],
  before => Exec['populate_db']
}

exec { 'populate_db': 
  command => 'mysql -u root --password="bartosz15"  -D modago < /vagrant/env/db.sql'
}

# IONCUBE

# exec{'copy_extension':
#  command => 'cp /var/www/env/ioncube/ioncube_loader_lin_5.4.so /usr/lib/php5/20100525', 
#  require => Class['php']
# }

# exec{'copy_ini':
#  command => 'cp /var/www/env/20-ioncube.ini /etc/php5/fpm/conf.d',
#  require => Exec['copy_extension']
# }

# SOLR
class { "solr": }

# MAGENTO
class { "magentolocal": }






# file{"${full_web_path}":
#  ensure => link,
#  target => '/vagrant'
# }

# Exec{path => '/usr/bin/:/usr/local/bin/'}

# exec{'run_composer':
#  cwd => '/vagrant/src/',
#  onlyif => 'test -f /vagrant/src/composer.json',
#  command => 'composer update',
#  require => Class['composer']
# } 


#class{'phpmyadmin':}
#class{'git':}
#class{'curl':}
#class{'composer':}
# class{'phing':}
# class{'phpdoc':}
# class{'phpqatools':}
