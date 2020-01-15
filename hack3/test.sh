cmd="nc oasis 26656"

#!/bin/bash
for i in $(seq 1 1 10000)
do
   echo "Welcome $i times"
   $cmd >/dev/null &
done
