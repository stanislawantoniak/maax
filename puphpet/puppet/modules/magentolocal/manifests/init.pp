class magentolocal{

	file { 'local.xml':
		path => '/vagrant/app/etc/local.xml',
		ensure => file,
		content => template("magentolocal/local.xml.erb")
	}
}
