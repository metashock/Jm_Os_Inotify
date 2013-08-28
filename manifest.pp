exec { "apt-get update":
    command => "/usr/bin/apt-get update"
}

package { 'php5-cli':
	ensure => present,
	require => Exec["apt-get update"]
} 

package { 'php-pear':
    ensure => present,
	require => Exec["apt-get update"]
}

package { 'php5-xdebug':
    ensure => present,
	require => Exec["apt-get update"]
}

package { 'make':
    ensure => present,
	require => Exec["apt-get update"]
}

exec { ' install php_inotify':
    command => '/usr/bin/pecl install inotify',
    creates => '/usr/lib/php5/20090626+lfs/inotify.so',
    require => [
        Package['php-pear'],
        Package['make']
    ]
}

file { '/etc/php5/conf.d/inotify.ini':
    content => "extension=inotify.so",
    ensure => file,
    require => Exec[' install php_inotify']
}


# pear package installation
# @see http://blog.code4hire.com/2013/01/pear-packages-installation-under-vagrant-with-puppet/

exec { "pear clear-cache" :
  command => "/usr/bin/pear clear-cache",
  require => [Package['php-pear']],
  returns => [ 0, 1]
}

# set channels to auto discover
exec { "pear auto_discover" :
  command => "/usr/bin/pear config-set auto_discover 1",
  require => [Package['php-pear']]
}

exec { 'pear update-channels':
    command => '/usr/bin/pear update-channels',
    require => Package['php-pear']
}

exec { 'install php unit':
    command => '/usr/bin/pear install --alldeps pear.phpunit.de/PHPUnit',
    creates => '/usr/bin/phpunit',
    require => Exec['pear update-channels']
}

exec { "pear set preferred state beta" :
  command => "/usr/bin/pear config-set preferred_state beta",
  require => [Package['php-pear']]
}

exec { 'install Jm_Log':
    command => '/usr/bin/pear install --alldeps www.metashock.de/pear/Jm_Log-0.1.0',
    creates => '/usr/share/php/Jm',
    require => [ 
	Exec['pear set preferred state beta'],
	Exec['pear auto_discover']
    ]
}


package { 'vim':
	ensure => present,
	require => Exec["apt-get update"]
}


file {'/root/.vimrc':
	content => "
syntax on
colorscheme desert
set expandtab
set ts=4
set number
",
	require => Package['vim']
}


file {'/home/vagrant/.vimrc':
	content => "
syntax on
colorscheme desert
set expandtab
set ts=4
set number
",
	require => Package['vim']
}
