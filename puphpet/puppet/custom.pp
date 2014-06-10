# IONCUBE

# exec{'copy_ioncube_extension':
#  command => 'cp /var/www/puphpet/ioncube/ioncube_loader_lin_5.4.so /usr/lib/php5/20100525', 
#  require => Class['php']
# }

# exec{'copy_ini':
#  command => 'cp /var/www/puphpet/20-ioncube.ini /etc/php5/fpm/conf.d',
#  require => Exec['copy_ioncube_extension']
# }

# SOLR
# class { "solr": }

# MAGENTO
# class { "magentolocal": }