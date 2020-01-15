#!/bin/bash
cmd="nc $1 $2"

for i in $(seq 1 1 2000)
do
   echo "Welcome $i times"
   $cmd 2>/dev/null >/dev/null &
done
