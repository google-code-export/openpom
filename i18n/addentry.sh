#!/bin/bash

#For each line in lserror

echo '
#------ Entry added by addentry.sh ------' >> messages.pot

nbLine=`wc -l lserror|awk '{ if(match($0, /([0-9]+) /, cap)) print cap[1]; }'`
for i in `seq 1 "$nbLine"`; do
#for i in `seq 1 4`; do

        #msg get the index to add
        msg=`sed -n "$i"p lserror`
	
	echo "msgid \"$msg\"" >> messages.pot
	echo "msgstr \"\"
" >> messages.pot	

done

echo '#------ End of the entries added by addentry.sh ------' >> messages.pot
