#!/bin/sh
#
# USAGE EXAMPLE
# sh shell/feedexport.sh --cron 1
#
# location of the php binary
CRONSCRIPT=feedexport.php

ARG1=""
if [ ! "$1" = "" ] ; then
	ARG1=" $1"
fi

ARG2=""
if [ ! "$2" = "" ] ; then
	ARG2=" $2"
fi

ARG3=""
if [ ! "$3" = "" ] ; then
	ARG3=" $3"
fi

PHP_BIN=`which php`

# absolute path to magento installation
INSTALLDIR=`echo $0 | sed 's/feedexport\.sh//g'`

#	prepend the intallation path if not given an absolute path
if [ "$INSTALLDIR" != "" -a "`expr index $CRONSCRIPT /`" != "1" ];then
    if ! ps auxwww | grep "$INSTALLDIR$CRONSCRIPT$ARG1$ARG2$ARG3" | grep -v grep 1>/dev/null 2>/dev/null ; then
    	$PHP_BIN $INSTALLDIR$CRONSCRIPT$ARG1$ARG2$ARG3 &
    fi
else
    if  ! ps auxwww | grep "$CRONSCRIPT$ARG1$ARG2$ARG3" | grep -v grep | grep -v feedexport.sh 1>/dev/null 2>/dev/null ; then
        $PHP_BIN $CRONSCRIPT$ARG1$ARG2$ARG3 &
    fi
fi
