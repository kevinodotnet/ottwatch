#!/bin/bash

cat ~/bylaw/bylaw.txt | while read l; do
	num=`echo $l | cut -d\| -f1`
	desc=`echo $l | cut -d\| -f2`
	date=`echo $l | cut -d\| -f3`
	echo "-----"
	echo $num

	php bylaw.php injestBylaw ~/bylaw/$num.pdf "$desc" $date

done

