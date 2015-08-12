<?php

return [
    'providers' => [
        'application' => \Baleen\Cli\Container\ServiceProvider\DefaultProvider::class,
        'storage' => \Baleen\Cli\Container\ServiceProvider\StorageProvider::class,
        'repository' => \Baleen\Cli\Container\ServiceProvider\RepositoryProvider::class,
        'timeline' => \Baleen\Cli\Container\ServiceProvider\TimelineProvider::class,
        'helperSet' => \Baleen\Cli\Container\ServiceProvider\HelperSetProvider::class,
        'commands' => \Baleen\Cli\Container\ServiceProvider\CommandsProvider::class,
    ],
];
