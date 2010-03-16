#!/bin/sh

VERSION=1.01

rm -rf mobilecat-$VERSION
mkdir mobilecat-$VERSION

rsync -a --exclude='mobilecat-*.tar.gz' --exclude='build.sh' --exclude='*.svn' --exclude="mobilecat-$VERSION" . mobilecat-$VERSION

tar --file=mobilecat-$VERSION.tar.gz --gzip --create mobilecat-$VERSION
rm -rf mobilecat-$VERSION

echo
echo "CREATED mobilecat-$VERSION.tar.gz"
