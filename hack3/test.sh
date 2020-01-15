#!/bin/bash
cmd="nc $1 $2"

for i in $(seq 1 1 1000)
do
   echo "Welcome $i times"
   $cmd >/dev/null &
done
