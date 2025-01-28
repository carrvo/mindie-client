#!/bin/bash

if [ "$1" == "" ];
then
	echo "usage: ./new-client.bash /path/to/client/root/"
	exit 1
fi

if [[ "$1" == *"./"* ]];
then
	echo "give absolute path, not relative!"
	exit 2
fi

if [[ "$1" != *"/" ]];
then
	echo "must end in a slash (/)!"
	exit 3
fi

touch "$1.htaccess"
sudo chgrp www-data "$1.htaccess"
sudo chmod u+r-wx,g+rw-x,o-rwx "$1.htaccess"
echo "<Directory $1>
	AllowOverride AuthConfig
</Directory>
SetEnv CLIENT_FILESYSTEM_PATH $1"

