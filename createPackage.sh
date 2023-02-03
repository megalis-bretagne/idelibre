#!/bin/bash

echo "generate installation package"

dest="/tmp/idelibre"
rm -rf $dest

mkdir $dest

cp -rfa docker-compose.yml .env.dist docker-resources initializeCertificates.sh  $dest

rm ${dest}/docker-resources/initApp.sh ${dest}/docker-resources/initAppTest.sh ${dest}/docker-resources/minimum.sql ${dest}/docker-resources/opcache.ini ${dest}/docker-resources/zz-idelibre.ini

sed -i -e"s|TAG|$CI_COMMIT_REF_NAME|" ${dest}/docker-compose.yml

echo "generate installation package DONE"