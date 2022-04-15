# README

# Exporting ottwatch.v1 database

```
MYUSER="root"
MYPASS="XXX"
MYSQLDUMP=" mysqldump --complete-insert --extended-insert=false -u $MYUSER --password=$MYPASS "

$MYSQLDUMP ottwatch \
	election \
	candidate \
	candidate_return \
	candidate_donation \
	> ottwatch_v1_snapshot.sql
```
