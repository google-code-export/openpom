#!/bin/bash

###############################################
# Creates .po and .pot files to translate pom #
###############################################
# ----- POT -----
# find all php files to create the .pot
find .. -xtype f -name "*.php" \
    |xgettext \
         --add-comments -L PHP --from-code=UTF-8 \
         -o messages.pot.new -f -

# merge with the old .pot
msgmerge --no-wrap -N messages.pot \
    messages.pot.new > messages.pot.merge

rm messages.pot.new

# .pot ok
mv messages.pot.merge messages.pot

./addentry.sh

# ----- PO -----
for i in `ls -d */ | awk '{ if(match($0, /(.+)\//, cap)) print cap[1]; }'`; do
  if [ -e messages-"$i".po ];then
    echo "$i"
    msgmerge --no-wrap -N messages-"$i".po messages.pot > messages-"$i".po.new
    mv messages-"$i".po.new messages-"$i".po
  fi
done

echo 'You can now translate the files in each .po and then launch trad2.sh to apply the changes'
