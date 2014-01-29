#!/bin/bash

for i in *pdf; do
	r=`echo $i | sed 's/\.pdf//'`
	if test -e $r; then
		echo "$r SKIPPING $r"
		continue;
	fi
	echo "$r PROCESSING"
	rm -rf $r
	mkdir -p $r
	pdftoppm -png $i $r/page
done
