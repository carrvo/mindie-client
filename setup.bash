#!/bin/bash

pushd /usr/local/lib/
sudo mkdir indieauth-client-php
sudo chown $USER indieauth-client-php
cd indieauth-client-php || exit
composer require indieauth/client
popd
touch indieauth-client-php/.htaccess
sudo chgrp www-data indieauth-client-php/.htaccess
sudo chmod u+r-wx,g+rw-x,o-rwx indieauth-client-php/.htaccess

