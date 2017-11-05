<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
ini_set('memory_limit', -1);
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}


$autoload = @include 'init_autoloader.php';

if (!class_exists('\PHPUnit_TextUI_Command')) {
    fwrite(STDERR, 'You need to set up the project dependencies using the following commands:' . PHP_EOL .
            'wget http://getcomposer.org/composer.phar' . PHP_EOL .
            'php composer.phar install' . PHP_EOL
    );
    die(1);
}

PHPUnit_TextUI_Command::main();
