load 'deploy'
# Uncomment if you are using Rails' asset pipeline
    # load 'deploy/assets'
load 'config/deploy' # remove this line to skip loading any of the default tasks

set :keep_releases, 3

after "deploy", "deploy:cleanup"
after "deploy", "copy_config"
desc "copy over configuration"
task :copy_config do
  run "cp #{settings_path}/config.php #{current_release}/lib/config.php"
end
