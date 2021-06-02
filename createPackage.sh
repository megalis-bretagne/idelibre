#!/bin/bash

echo "generate installation package"

dest="/tmp/idelibre"
rm -rf $dest

mkdir $dest

cp -rfa docker-compose.yml .env docker-resources initializeCertificate.sh  $dest
