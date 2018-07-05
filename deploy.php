<?php
namespace Deployer;

# https://github.com/deployphp/deployer/blob/master/recipe/laravel.php
require 'recipe/laravel.php';
require 'vendor/deployer/recipes/recipe/slack.php';

task('npm:install', 'npm install');
task('npm:build', 'npm run production');
task('clear:opcache', 'curl -s https://bibrex.uio.no/clear_opcache');

// Hosts
inventory('hosts.yml');

// Project name
set('application', 'bibrex');

// Number of releases to keep
set('keep_releases', 3);

set('ssh_multiplexing', true);

// Project repository
set('repository', 'https://github.com/scriptotek/bibrex.git');

set('slack_webhook', 'https://hooks.slack.com/services/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');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('http_user', 'nginx');

// Shared files/dirs between deploys
add('shared_dirs', ['storage']);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

after('deploy:vendors', 'npm:install');
after('npm:install', 'npm:build');

before('deploy:symlink', 'artisan:migrate');

// before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');

// after('deploy:symlink', 'clear:opcache');