<?php

$host_url = getenv('HOST_URL');
$config = [
    'prod'         => [
        'slack_url'                 => 'https://slack.com/oauth/authorize',
        'slack_oauth_url'           => 'https://slack.com/api/oauth.access',
        'client_id'                 => getenv('CLIENT_ID'),
        'client_secret'             => getenv('CLIENT_SECRET'),
        'scope'                     => 'commands,chat:write:bot,bot',
        'redirect_uri'              =>  $host_url . 'oauth/verifyOAuth',
        'installed_redirect_uri'    =>  $host_url . 'install/installed'
    ]
];