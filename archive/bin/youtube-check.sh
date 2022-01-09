#!/bin/bash

base=`dirname $0`
base="$base/.."
export PYTHONPATH=$PYTHONPATH:$base/lib/gdata/src

title="Transportation Committee"
desc="$title, 2013-04-03, http://ottwatch.ca/meetings/meeting/2232"
user_email="$2"
user_passwd="$3"

python \
	$base/lib/youtube-upload/youtube_upload/youtube_upload.py \
  "--check-status=$1" \
	"--email=$user_email" "--password=$user_passwd" \

