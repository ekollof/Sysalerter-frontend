#!/bin/ksh

HOSTLIST=`./hostlist.php`
CMD=$1

for i in ${HOSTLIST}
do
	ip=`echo $i | /bin/cut -f 1 -d :`
	hostname=`echo $i | /bin/cut -f 2 -d :`
	echo "${hostname} (${ip})"
	/usr/bin/scp install.sh root@${ip}:/tmp/install.sh
	/usr/bin/ssh root@${ip} "${CMD}"
done
