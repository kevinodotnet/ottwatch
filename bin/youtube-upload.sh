#!/bin/bash

base=`dirname $0`
base="$base/.."
export PYTHONPATH=$PYTHONPATH:$base/lib/gdata/src

title="Transportation Committee"
desc="$title, 2013-04-03, http://ottwatch.ca/meetings/meeting/2232"
user_email="email@gmail.com or YOUTUBE username"
user_passwd="your password"
video_file="some file reference"

python \
	$base/lib/youtube-upload/youtube_upload/youtube_upload.py \
	"--email=$user_email" "--password=$user_passwd" \
	--title="$title" --description="$desc" \
	--category=News --keywords="ottwatch, Ottawa City Council" \
	$video_file

