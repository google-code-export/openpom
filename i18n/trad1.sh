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
for i in messages-*.po; do
    echo "$i"
    msgmerge --no-wrap -N "$i" messages.pot > "$i".new
    mv "$i"{.new,}
done

echo 'You can now translate the files in each .po and then launch trad2.sh to apply the changes'
