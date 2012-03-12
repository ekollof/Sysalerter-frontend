#!/bin/ksh

TIMEOUT=10;
HOSTLIST=`./hostlist.php`
CMD="pkill sysalert; sleep 2; sysalert -l";

for i in ${HOSTLIST}
do
	ip=`echo $i | /bin/cut -f 1 -d :`
	hostname=`echo $i | /bin/cut -f 2 -d :`
	echo "${hostname} (${ip})"
	/usr/bin/ssh root@${ip} "${CMD}" & (sleep ${TIMEOUT} ; kill $!) & 2>/dev/null > /dev/null
done
