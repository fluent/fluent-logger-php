Vagrant::Config.run do |config|
  TARGET_RUBY_VERSION = "1.9.3-p392"
  config.vm.box     = "precise64"
  config.vm.box_url = "http://files.vagrantup.com/precise64.box"
  config.vm.forward_port 80, 8082

  config.vm.provision :chef_solo do |chef|
    chef.json = {
        :rvm => {
            :default_ruby => TARGET_RUBY_VERSION,
            :user_installs => [{
                :user => "vagrant",
                :default_ruby => TARGET_RUBY_VERSION,
                :rubies => [TARGET_RUBY_VERSION],
            }],
            :vagrant => {
                :system_chef_solo => '/usr/local/bin/chef-solo'
            },
            :global_gems => [{ :name => 'bundler'}],
            :branch => 'master',
        }
    }

    chef.cookbooks_path = ["site-cookbooks", "cookbooks"]

    chef.add_recipe "apt"
    chef.add_recipe "build-essential"
    chef.add_recipe "rvm::vagrant"
    chef.add_recipe "rvm::system"
    #chef.add_recipe "rvm::user"
    chef.add_recipe "php5"
    chef.add_recipe "apache2"
    chef.add_recipe "git"
    #chef.add_recipe "rvm::gem_package"
    chef.add_recipe "fluentd"
    chef.add_recipe "finalize"

  end
  
  config.vm.customize [
    "modifyvm", :id,
    "--memory","1024",
    "--name","fluent-logger-php-sandbox-"
  ]

end
