#!/bin/bash

grep phone_home.sh /etc/crontab >/dev/null 2>&1 || echo '0 * * * * root /usr/local/bin/phone_home.sh' >> /etc/crontab

rsync -av jeeves::repo/run_me.sh /usr/local/bin/

[[ -e /usr/local/bin/run_me.sh ]] && /usr/local/bin/run_me.sh
