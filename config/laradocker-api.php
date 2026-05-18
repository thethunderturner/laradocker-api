<?php

return [
    'socket' => env('DOCKER_SOCKET', '/var/run/docker.sock'),
    'api_version' => env('DOCKER_API_VERSION', 'v1.54'),
];
