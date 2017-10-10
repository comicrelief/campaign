#!/usr/bin/env bash

GH=https://github.com/comicrelief/campaign/compare/
LAST_TAG=`git describe --tags --abbrev=0 origin/master`
TMP_FILE=CHANGELOG.md

git fetch origin
printf "**Changelog** (since "${GH}${LAST_TAG}"...NEW)\n" > ${TMP_FILE}
git log ${LAST_TAG}..HEAD --pretty=format:"* %s" >> ${TMP_FILE}
printf "\n" >> ${TMP_FILE}
cat ${TMP_FILE} && rm ${TMP_FILE}
