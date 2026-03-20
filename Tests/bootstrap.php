<?php

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install" ?');
}

require $autoloadFile;

// Hack because of https://github.com/symfony/symfony/issues/53812#issuecomment-1962740145
// Make EntitiesHasDispatcherCheckerTest::testAnEntityThatDoesntHaveDispatcherWhileFlushedThrowAnError test passes
use Symfony\Component\ErrorHandler\ErrorHandler;
set_exception_handler([new ErrorHandler(), 'handleException']);

