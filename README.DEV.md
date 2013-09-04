#Jm_Os_Inotify

This document describes how you can easily setup a standardized development environment for `Jm_Os_Inotify`.

## Using Vagrant and Puppet

For those who want to have a look into the code and maybe change or debug it I've prepared and Vagrand ubuntu 12.04 test environemt. The vagrant configuration utilizes Puppet for configuration of the virtual machine. So you have to make sure both Vagrant and Puppet are installed on your development machine. Once you made this sure you can step into the development environemt using:

    $ git clone git://github.com/metashock/Jm_Os_Inotify.git
    $ cd Jm_Os_Inotify
    $ vagrant up
    $ vagrant ssh

Then in vagrant move to the /vagrant folder. It contains the files clone by git before.

    $ cd /vagrant/

If you want to execute the test suite for example run:

    $ cd /vagrant
    $ phpunit


