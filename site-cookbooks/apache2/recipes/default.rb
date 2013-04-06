#
# Cookbook Name:: apache2
# Recipe:: default
#
# Copyright 2012, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#
package 'libapache2-mod-php5'

script "enable_modules" do
  interpreter "bash"
  user "root"
  cwd "/tmp"
  code <<-EOH
    a2dissite 000-default
    a2enmod rewrite
  EOH
end
