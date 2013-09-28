#!/bin/bash

###########################################
#          Apply the translations         #
# Be careful, this script restart httpd ! #
###########################################


for i in messages-*.po; do
    echo "$i"
    localedir=${i#messages-}
    localedir=${localedir%.po}
    mkdir -p "$localedir/LC_MESSAGES"
    msgfmt -o "$localedir/LC_MESSAGES/messages.mo" "$i"
done

[ "$NO_HTTPD_RESTART" == "1" ] || service httpd restart

