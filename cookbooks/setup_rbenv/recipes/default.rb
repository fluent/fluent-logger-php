#
# Cookbook Name:: finalyze
# Recipe:: default
#
# Copyright 2012, YOUR_COMPANY_NAME
#
# All rights reserved - Do Not Redistribute
#
require_recipe "ruby_build"
require_recipe "rbenv::system"

rbenv_global "1.9.3-p0"