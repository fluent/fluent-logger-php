#
# Cookbook Name:: ./
# Recipe:: default
#
# Copyright 2012, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#

directory "/etc/fluent" do
  owner "vagrant"
  group "vagrant"
  mode "0755"
  action :create
end

cookbook_file "/etc/fluent/fluent.conf" do
  owner "vagrant"
  group "vagrant"
  source "fluentd"
end