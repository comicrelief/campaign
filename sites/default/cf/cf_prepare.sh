#!/usr/bin/env bash

set -e

authenticate(){
  cf api $CF_API
  cf auth $CF_USERNAME $CF_PASSWORD
}

create_orgs(){
  cf create-org comicrelief
  cf target -o comicrelief
}

create_services(){
  cf create-user-provided-service service-mysql-1 -p "{\"hostname\":\"cr-rnd17-drupal-eu-west-1.cbchyxc5kvoe.eu-west-1.rds.amazonaws.com\",\"name\":\"drupal$CF_SPACE\",\"username\":\"drupal\",\"password\":\"qvs6AN6CJEHeBAUwZ2pj\"}" || true
  cf create-service rediscloud 30mb service-redis-1
}

create_services_fail(){
  echo "Services failed to create"
}

create_space(){
  cf create-space $CF_SPACE
  cf target -s $CF_SPACE
}

authenticate
create_orgs
create_space
create_services || create_services_fail
