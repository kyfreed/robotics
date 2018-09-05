#!/bin/bash

#test on server by running:     sudo sh /var/www/html/autoupdate.sh
#check last minute's run with:  more /var/www/html/last_autoupdate_run.txt

date

wget https://api.github.com/repos/kyfreed/robotics/git/refs/heads/master -O /var/www/html/latest_head.json >/dev/null 2>&1
touch /var/www/html/latest_head.json
if diff /var/www/html/latest_head.json /var/www/html/last_head.json > /dev/null ; then
	echo "  ---- No change in master ----"
else
	echo "  ==== Updating master ===="
	cd /var/www/html
	cp latest_head.json last_head.json
	sudo -u ec2-user git pull
fi
