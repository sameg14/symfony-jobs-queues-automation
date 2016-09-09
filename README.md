Jobs, Queues & Automation
==============================

In order to run the demo locally you will need to install the following software

- [virtualbox](https://www.virtualbox.org/wiki/Downloads)
- [vagrant](https://www.vagrantup.com/downloads.html)
- [homebrew](http://brew.sh/)
- ansible `brew install ansible`

Once you have the software installed clone this repository and run `cd symfony-jobs-queues-automation` `vagrant up`

After the process is finished, you can SSH into the machine using `vagrant ssh`

You can use supervisor crontrol to manage the worker processes `sudo supervisorctl`

The code will be mounted to a virtual folder located in `/var/www/app` on the VM. So any changes you make to this repo on your mac will be immedietly available to you in the VM

