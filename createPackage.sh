#!/bin/bash

echo "generate installation package"

dest="/tmp/idelibre"
rm -rf $dest

mkdir $dest

cp -rfa docker-compose.yml .env docker-resources initializeCertificates.sh  $dest

sed -i -e"s|TAG|$CI_COMMIT_REF_NAME|" ${dest}/docker-compose.yml
