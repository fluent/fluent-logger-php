Vagrant::Config.run do |config|
  config.vm.box     = "ubuntu-1104-server-amd64"
  config.vm.box_url = "http://dl.dropbox.com/u/7490647/talifun-ubuntu-11.04-server-amd64.box"
  config.vm.forward_port 80, 8082

  config.vm.provision :chef_solo do |chef|
    chef.cookbooks_path = "cookbooks"
    chef.add_recipe "apt"
    chef.add_recipe "php5"
    chef.add_recipe "apache2"
    chef.add_recipe "git"
    chef.add_recipe "build-essential"
    chef.add_recipe "setup_rbenv"
    chef.add_recipe "fluentd"
    chef.add_recipe "finalize"
  end
  
  config.vm.customize [
    "modifyvm", :id,
    "--memory","1024",
    "--name","fluent-logger-php-sandbox"
  ]

end