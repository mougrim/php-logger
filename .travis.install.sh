#!/usr/bin/env bash
if [ `php -r 'echo (int)(version_compare(PHP_VERSION, "7.0.0") >= 0);'` -eq "1" ]
then
    pecl install uopz
else
    rm -rf runkit*
    git clone https://github.com/zenovich/runkit.git
    cd runkit
    phpize
    ./configure
    make
    make install
    echo "extension=runkit.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    echo "runkit.internal_override=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi
