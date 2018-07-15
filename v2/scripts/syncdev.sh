#!/bin/bash
# déploiement de la version xxx svn vers le rep zord (par défaut)
LOCALPATH=/REP MASTER
LOCALPROD=/REP DEV
HOSTUSER=USER
HOSTNAME=HOST
HOSTPATH=/REP HOST
#
DATE=$(date "+%d_%m_%Y.%H")
if [ $# -ge 2 ] # si nb d'arguments >= 2
then
	if [ $1 = $2 ]
	then
		echo origine et source identique
	else
		case "$1" in
		"master")
		SRC=$LOCALPATH/  ;;
		"dev")
		SRC=$LOCALPROD/ ;;
		"zordania")
		SRC=$HOSTUSER@$HOSTNAME:$HOSTPATH/zordania ;;
		"zorddev")
		SRC=$HOSTUSER@$HOSTNAME:$HOSTPATH/zorddev ;;
		*)
		SRC="" ;;
		esac
		case "$2" in
		"master") 
		DEST=$LOCALPATH ;;
		"dev")
		DEST=$LOCALPROD ;;
		"zordania")
		DEST=$HOSTUSER@$HOSTNAME:$HOSTPATH/zordania ;;
		"zorddev")
		DEST=$HOSTUSER@$HOSTNAME:$HOSTPATH/zorddev ;;
		*)
		DEST="" ;;
		esac

		if [ "$SRC" = "" -o "$DEST" = "" ]
		then
			echo usage: $0 TO FROM [force]
			echo valeurs possibles pour TO et FROM:
			echo "- git (local git branche dev)"
			echo "- gitm (local git branche master)"
			echo "- zordania (zordania prod)"
			echo "- zorddev (zordania dev)"
		elif [ $# = 3 ]
		then
			if [ $3 = force ]
			then
				rsync -rtvzC $SRC $DEST --exclude-from='exclude.rsync' > $LOCALPATH/logs/mep/$1_to_$2_$DATE.log
			else
				echo "3e argument = force (ou vide)"
			fi
		else # 2 arguments seulement: simulation -n
			rsync -rtvzCn $SRC $DEST --exclude-from='exclude.rsync' > $LOCALPATH/logs/mep/simul_$1_to_$2_$DATE.log
			cat $LOCALPATH/logs/mep/simul_$1_to_$2_$DATE.log
		fi
	fi
else
	echo usage: $0 TO FROM [force]
	echo valeurs possibles:
	echo "- cvs (local)"
	echo "- userver (dev)"
	echo "- zordania (prod)"
	echo "- zorddev (dev)"
fi

