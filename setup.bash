#!/bin/bash

pushd /usr/local/lib/
sudo mkdir indieauth-client-php
sudo chown $USER indieauth-client-php
cd indieauth-client-php || exit
composer require indieauth/client
popd

