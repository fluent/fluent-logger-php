#
# Cookbook Name:: ./
# Recipe:: default
#
# Copyright 2012, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#
rbenv_gem "fluentd" do
  rbenv_version   "1.9.3-p0"
  action          :install
end