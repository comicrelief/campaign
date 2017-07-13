#!/usr/bin/env bash

NEW_TAG="NEW_RELEASE_ID"
TAG_LAST=`git describe --tags --abbrev=0 origin/master`
TMP_FILE=release.txt

git fetch origin
echo "**Changelog** (since ["${TAG_LAST}"..."${NEW_TAG}"]("${TAG_LAST}"..."${NEW_TAG}"))" > ${TMP_FILE}
echo "" >> ${TMP_FILE}
git log ${TAG_LAST}..HEAD --pretty=format:"- %s" origin/develop >> ${TMP_FILE}
echo "" >> ${TMP_FILE}
cat release.txt && rm ${TMP_FILE}
