#!/bin/bash

set -e

if [ -z "$1" ]
then
  echo "Database is necessary"
  exit 1
fi

n=0;
max=10
secs=6
while [ -z "$(docker ps -q -f health=healthy -f name=anydataset_db_$1)" ] && [ "$n" -lt "$max" ];
do
  echo "Waiting for $1...";
  n=$(( n + 1 ))
  sleep $secs;
done

if [ "$n" -gt "$max" ]
then
  echo "$mysql was not health after $(( max * secs ))"
  exit 2
fi

echo "$1 is up"

docker ps


