#!/usr/bin/env bash
PATH=`dirname "$(readlink -f "$0")"`/tests/bin:$PATH
# create cache dir for soft-mocks
[ -d /tmp/mocks/ ] || mkdir /tmp/mocks/

if [ `./.travis.check-php-version-coverage.php` -eq "1" ]; then
  echo 'enable coverage'
  echo "xdebug.coverage_enable=On" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
else
  XDEBUG_INI="/home/travis/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini"
  if [ -f "$XDEBUG_INI" ]; then
    echo 'remove xdebug config'
    phpenv config-rm xdebug.ini
  else
    echo "xdebug config isn't exists"
  fi
fi
