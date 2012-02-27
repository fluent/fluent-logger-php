require 'rubygems'
require 'tempfile'
require 'json'

namespace :fluent do

  desc "check testcase"
  task :test do
    `phpunit`
  end

  task :default do
  end
  
  desc "create pear package"
  task :package do
    log "# creating package...\n"
    
    io = open("|pear package")
    while !io.eof
      tmp = io.gets
      print tmp
    end
    io.close
    /^Package (.+?) done/ =~ tmp
    
    ENV['PACKAGE_NAME'] = $1
    FileUtils::mkdir_p("builds")
    FileUtils::mv($1, "builds")
  end
  
  desc "install fluent-logger-php via pear"
  task :install => [:package] do
    log "# install #{ENV['PACKAGE_NAME']} with pear\n"
    Dir::chdir("builds") do 
      io = open("|pear install #{ENV['PACKAGE_NAME']}")
      while !io.eof
        print io.gets
      end
      io.close
    end
  end
  
  desc "remove packaged files."
  task :clean do
    Dir::chdir("builds") do 
      Dir::foreach(".") do |file|
        if !FileTest.directory?(file) then
          File.delete(file)
        end
      end
    end
  end
  
  def log(message)
    print message
  end

  desc "create api docs with apigen"
  task :docs do
    ref = "refs/heads/gh-pages"
    tags = `git tag`.split("\n")
    tags.push("develop")
    
    docs = "docs"
    tags.each { |v|
      log "  - processing version #{v}"
      workdir = mkdir_temp
      checkout(v, workdir)
      `apigen --source #{workdir} --destination docs/#{v} --title "Fluent-Logger-PHP #{v}"`
    }
    with_git_env(docs) do
      psha = `git rev-parse gh-pages 2>/dev/null`.chomp
      `git add -A`
      tsha = `git write-tree`.chomp
      if(psha == ref)
        csha = `echo 'generated docs' | git commit-tree #{tsha}`.chomp
      else
         csha = `echo 'generated docs' | git commit-tree #{tsha} -p #{psha}`.chomp
      end
      puts "\twrote commit #{csha}"
      `git update-ref -m 'generated docs' #{ref} #{csha}`
      puts "\tupdated"    end
  end

  desc "generate contributors list"
  task :contributors do
    print `git log --format='%aN' | sort -u`
  end

  def checkout(version, workdir)
    with_git_env(workdir) do
      `git read-tree #{version}:src`
      `git checkout-index -a`
    end
  end

  def mkdir_temp
    tf = Tempfile.new('apigen-tmp')
    tpath = tf.path
    tf.unlink
    FileUtils.mkdir_p(tpath)
    tpath
  end

  def mkfile_temp
    tf = Tempfile.new('apigen-index')
    tpath = tf.path
    tf.unlink
    tpath
  end

  def with_git_env(workdir)
    ENV['GIT_DIR'] = File.join(Dir::pwd, '.git')
    ENV['GIT_INDEX_FILE'] = mkfile_temp
    ENV['GIT_WORK_TREE'] = workdir
    yield
    ENV.delete('GIT_INDEX_FILE')
    ENV.delete('GIT_WORK_TREE')
    ENV.delete('GIT_DIR')
  end
end