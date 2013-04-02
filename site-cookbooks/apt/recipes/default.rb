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

package "libreadline6-dev"
package "libyaml-dev"
package "libsqlite3-dev"
package "sqlite3"
package "libxml2-dev"
package "libxslt1-dev"
package "libgdbm-dev"
package "libncurses5-dev"
package "pkg-config"
package "libffi-dev"
