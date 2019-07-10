<?php
namespace Deployer;

with(new \Dotenv\Dotenv(__DIR__))->load();

# https://github.com/deployphp/deployer/blob/master/recipe/laravel.php
require 'recipe/laravel.php';
require 'recipe/sentry.php';
require 'vendor/deployer/recipes/recipe/slack.php';

task('npm:install', 'npm install');
task('npm:build', 'npm run production');
task('bibrex:version-notify', 'php artisan bibrex:version-notify');
task('self-diagnosis', 'php artisan self-diagnosis');

// Hosts
inventory('hosts.yml');

// Project name
set('application', 'bibrex');

// Number of releases to keep
set('keep_releases', 3);

set('ssh_multiplexing', true);

// Project repository
set('repository', 'https://github.com/scriptotek/bibrex.git');

set('slack_webhook', $_ENV['SLACK_HOOK']);

set('sentry', [
    'organization' => 'uio-realfagsbiblioteket',
    'project' => 'bibrex',
    'token' => $_ENV['SENTRY_TOKEN'],
    // 'version' => '1.0.0'
]);

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
before('deploy:symlink', 'artisan:route:cache');

after('deploy:symlink', 'artisan:queue:restart');
after('deploy:symlink', 'self-diagnosis');

after('deploy:failed', 'slack:notify:failure');
after('success', 'slack:notify:success');
after('success', 'bibrex:version-notify');

// after('deploy', 'deploy:sentry');

// Note to self: We don't make any attempt of clearing opcache since we assume that opcache.revalidate_path=1 is set.
