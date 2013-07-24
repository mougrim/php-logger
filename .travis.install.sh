rm -rf runkit*
git clone https://github.com/zenovich/runkit.git
cd runkit
phpize
./configure
make
make install
echo "extension=runkit.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "runkit.internal_override=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
