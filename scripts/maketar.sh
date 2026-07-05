VERSION=1.4.0
cd ../..
tar cf therp/therp-$VERSION.tar therp/** --exclude-from therp/scripts/exclude
gzip therp/therp-$VERSION.tar
cd therp/scripts
