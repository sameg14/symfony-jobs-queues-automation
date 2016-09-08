VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

    config.vm.hostname = "jobdemo.vm"
    config.vm.box = "ubuntu/trusty64"

    config.vm.network "private_network", ip: "10.10.10.123"
    config.vm.synced_folder ".", "/var/www/app"

    config.vm.provider "virtualbox" do |vb|
        vb.gui = false
        vb.customize ["modifyvm", :id, "--memory", "1024"]
        vb.customize ["modifyvm", :id, "--cpus", "2"]
    end

    config.vm.provision "ansible" do |ansible|
        ansible.limit = 'all'
        #ansible.verbose = "vvvv"
        ansible.inventory_path = 'infrastructure/hosts-dev'
        ansible.playbook = "infrastructure/site.yml"
        ansible.extra_vars = { ansible_ssh_user: 'vagrant', symfony_env: 'dev', nginx_user: 'vagrant', redis_ip: '127.0.0.1', redis_port: 6379  }
    end
end
