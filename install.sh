#!/bin/bash

#
# Only installs PHP
#

SERVER='jspacco@cs.knox.edu:webpage/iclicker'

cd iclicker

LIST=''
FILES=`ls *php`
for f in $FILES; do
    if [ "$f" != "dbutils.php" ]
    then
	#echo $f
	LIST="$LIST $f"
    fi
done

#echo $LIST
scp $LIST $SERVER

#cd ..
