#
# Cookbook Name:: apt
# Recipe:: default
#
# Copyright 2012, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#

script "update_apt" do
  interpreter "bash"
  user "root"
  cwd "/tmp"
  code <<-EOH
    apt-get update
  EOH
end
