#!/bin/ksh
#

# (SunOS/i86pc/sysalert) = 404662403c1b03594aabf5599fb9d8af
# (SunOS/sun4u/sysalert) = a60a1bbb903cebd4e49853ed7f0b93ad
# (Linux/i386/sysalert) = e85d1c90510ce028d903b9e940585fb8
# (FreeBSD/i386/sysalert) = a12de178ed64d463f9ddf4afc21fa63d

MD5_LINUX_I386="e85d1c90510ce028d903b9e940585fb8"
MD5_SOLARIS_X86="404662403c1b03594aabf5599fb9d8af"
MD5_SOLARIS_SPARC="a60a1bbb903cebd4e49853ed7f0b93ad"
MD5_FREEBSD_I386="a12de178ed64d463f9ddf4afc21fa63d"

PROC=`uname -m`
OS=`uname -s`

# find http fetcher
if [ "${OS}" = "SunOS" ]
then
	WGET="/usr/sfw/bin/wget";
else
	WGET="/usr/bin/wget";
fi

case ${OS} in
	SunOS)
		WGET="/usr/sfw/bin/wget"
		;;
	Linux)
		WGET="/usr/bin/wget"
		;;
	FreeBSD)
		WGET="/usr/bin/fetch"
		;;
	*)
		echo "Unsupported operating system"
		exit;
		;;
esac

# find grep

if [ -e /tmp/skipme ]
then
	echo "skipfile found, bailing";
	exit
fi

echo "Installing system alerter for ${OS} on ${PROC}...";
rm -f /tmp/sysalert
pkill sysalert
sleep 2

OLDPWD=`pwd`;
cd /tmp
${WGET} http://stats.otlupc.net/bin/${OS}/${PROC}/sysalert
if [ $? != 0 ]
then
	echo "Couldn't fetch binary :("
	exit 1
fi

# Compare MD5 sums
case ${OS} in 
	SunOS)
		MD5=`digest -a md5 /tmp/sysalert`
		;;
	Linux)
		MD5=`md5sum /tmp/sysalert | awk '{print $1}'`
		;;
	FreeBSD)
		MD5=`md5 -q /tmp/sysalert`
		;;
	*)
		echo "Not supported."
		exit;
		;;
esac

if [ "${OS}" = "SunOS" ]
then
	if [ "${PROC}" = "i86pc" ]
	then
		if [ "${MD5}" != "${MD5_SOLARIS_X86}" ]
		then
			echo "Checksum error, bailing"
			exit
		fi
	fi
	if [ "${PROC}" = "sun4u" ]
	then
		if [ "${MD5}" != "${MD5_SOLARIS_SPARC}" ]
		then
			echo "Checksum error, bailing"
			exit
		fi
	fi
	if [ "${PROC}" = "sun4v" ]
	then
		if [ "${MD5}" != "${MD5_SOLARIS_SPARC}" ]
		then
			echo "Checksum error, bailing"
			exit
		fi
	fi
fi
if [ "${OS}" = "Linux" ]
then
	# assume i386, nice huh? :)
	if [ "${MD5}" != "${MD5_LINUX_I386}" ]
	then
		echo "Checksum error, bailing"
		exit
	fi
fi
if [ "${OS}" = "FreeBSD" ]
then
	# assume i386, nice huh? :)
	if [ "${MD5}" != "${MD5_FREEBSD_I386}" ]
	then
		echo "Checksum error, bailing"
		exit
	fi
fi

cp /tmp/sysalert /usr/bin/sysalert
chmod 755 /usr/bin/sysalert
echo "Installation succesful."
cd ${OLDPWD}
pkill sysalert
/usr/bin/sysalert -l

SSH_PUBKEY="ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAoO/b8gKvwPXdLy6w4oG9GwzhwuABWjf3nY5u5/yOk/lJ7ysIO4/fpTVvw0DK3YETNfkXNJcUkX6vvoovUUZvKLC6vWAp+p9SYPCDOuVmQJbKh2lOBLATgAHrfTYwJz/WVju3nZQK6ejeQMWz+fQGqdA7inP3N3fjljEbtjkI/cSF5iPpFH/BzYEv3/ejSl7t2x2C364OCvrKm67A7wND9+NQ5A6jxNF9U0WnIVAi82Lof0JQWdJ7X84i0VOJzztKh80r6DbIAoYdfplDbZ+qsYSp//56FF2h74E7Bi6sfc7zEyrFK/GiAMQRgNmWfotnOUdDxoLMJaAOO8IwIEKwEQ== root@otl-thumper01"

OLDPWD=`pwd`;
# installing ssh key
mkdir -p ~/.ssh/

KEY=`echo ${SSH_PUBKEY} | awk '{print $2}'`;
CHECK=`grep ${KEY} ~/.ssh/authorized_keys | wc -l 2>/dev/null`; 

if [ "${CHECK}" -ne "0" ]
then
	echo "SSH key present: ${CHECK}"
	exit 0
fi

echo ${SSH_PUBKEY} >> ~/.ssh/authorized_keys
exit
