rm -r runkit
mkdir runkit
cd runkit
curl -O http://cloud.github.com/downloads/zenovich/runkit/runkit-1.0.3.tgz
tar -xzf runkit-1.0.3.tgz
cd runkit-1.0.3
ls -lah
phpize
./configure
make
make install

