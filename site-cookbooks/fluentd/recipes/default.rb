#
# Cookbook Name:: ./
# Recipe:: default
#
# Copyright 2012, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#

#include_recipe "rvm::user_install"
#include_recipe "rvm::vagrant"

rvm_gem "fluentd" do
  ruby_string "ruby-1.9.3-p392"
  action      :install
end
