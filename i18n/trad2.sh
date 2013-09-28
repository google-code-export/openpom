#!/bin/bash

###########################################
#          Apply the translations         #
# Be careful, this script restart httpd ! #
###########################################


for i in `ls -d */ | awk '{ if(match($0, /(.+)\//, cap)) print cap[1]; }'`; do
  if [ -e messages-"$i".po ];then
    echo "$i"
    msgfmt -o "$i"/LC_MESSAGES/messages.mo messages-"$i".po
  fi
done

[ "$NO_HTTPD_RESTART" == "1" ] || service httpd restart

