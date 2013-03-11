# Name of App and GIT location
set :application, "ottwatch"
set :repository,  "ssh://prodweb/mnt/git/ottwatch.git"

set :home_path, "/mnt/shared/ottwatch"
set :deploy_via, :remote_cache
set(:deploy_to) { "#{home_path}" }

set :settings_path, "/mnt/shared/ottwatch/settings"

namespace :deploy do
  task :restart do
  end
end

set :keep_releases, 10
set :normalize_asset_timestamps, false

set :copy_exclude, ["public","tmp"]

set :scm, :git # You can set :scm explicitly or Capistrano will make an intelligent guess based on known version control directory names
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `git`, `mercurial`, `perforce`, `subversion` or `none`

role :web, "prodweb"                          # Your HTTP server, Apache/etc
#role :app, "your app-server here"                          # This may be the same as your `Web` server
#role :db,  "your primary db-server here", :primary => true # This is where Rails migrations will run
#role :db,  "your slave db-server here"

# if you want to clean up old releases on each deploy uncomment this:
# after "deploy:restart", "deploy:cleanup"

# if you're still using the script/reaper helper you will need
# these http://github.com/rails/irs_process_scripts

# If you are using Passenger mod_rails uncomment this:
# namespace :deploy do
#   task :start do ; end
#   task :stop do ; end
#   task :restart, :roles => :app, :except => { :no_release => true } do
#     run "#{try_sudo} touch #{File.join(current_path,'tmp','restart.txt')}"
#   end
# end
