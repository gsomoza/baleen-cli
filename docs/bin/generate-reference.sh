#!/bin/bash
EXECPATH=`dirname $0`
cd $EXECPATH
cd ..

rm -rf en/reference/Baleen
mkdir -p en/reference/Baleen
../vendor/bin/sphpdox process -o en/reference Baleen\\Cli ../src
find en/reference -name "*.rst" -exec bash -c 'mv "$1" "$(sed "s/\.rst$/.txt/" <<< "$1")"' - '{}' \;
