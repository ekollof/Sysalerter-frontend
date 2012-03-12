#!/bin/ksh
#

SSH_PUBKEY="ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAoO/b8gKvwPXdLy6w4oG9GwzhwuABWjf3nY5u5/yOk/lJ7ysIO4/fpTVvw0DK3YETNfkXNJcUkX6vvoovUUZvKLC6vWAp+p9SYPCDOuVmQJbKh2lOBLATgAHrfTYwJz/WVju3nZQK6ejeQMWz+fQGqdA7inP3N3fjljEbtjkI/cSF5iPpFH/BzYEv3/ejSl7t2x2C364OCvrKm67A7wND9+NQ5A6jxNF9U0WnIVAi82Lof0JQWdJ7X84i0VOJzztKh80r6DbIAoYdfplDbZ+qsYSp//56FF2h74E7Bi6sfc7zEyrFK/GiAMQRgNmWfotnOUdDxoLMJaAOO8IwIEKwEQ== root@otl-thumper01"

OLDPWD=`pwd`;
# installing ssh key
mkdir -p ~/.ssh/

KEY=`echo ${SSH_PUBKEY} | /usr/bin/awk '{print $2}'`;
CHECK=`/usr/bin/grep ${KEY} ~/.ssh/authorized_keys | wc -l 2>/dev/null`; 

if [ "${CHECK}" -ne "0" ]
then
	echo "SSH key present: ${CHECK}"
	exit 0
fi

echo ${SSH_PUBKEY} >> ~/.ssh/authorized_keys
exit
