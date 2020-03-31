#!/bin/bash
PIDFILE="/var/www/vizplus/info.viz.plus/module/ops_linking.pid"
if test -f "$PIDFILE"; then
	PID=`cat $PIDFILE`
	echo "PIDFILE exist $PID"
	PIDSTATUS=`ps -aux | grep [o]ps_linking.php | awk '{print $2}' | grep -v '^${PID}$'`
	echo "======="
	echo "PIDSTATUS equal $PIDSTATUS"
	if [ "$PID" == "$PIDSTATUS" ]; then
		echo "equal!"
	else
		echo "not equal!"
	fi
	if [ "$PID" == "$PIDSTATUS" ]; then
		echo "PID exist $PID, terminate file and wait..."
		rm -rf "$PIDFILE"
		MAXSLEEP=15000
		SLEEPSTEP=10
		while [ $MAXSLEEP -gt 0 ]
		do
			echo "Waiting ${MAXSLEEP}ms..."
			sleep 0.1s
			MAXSLEEP=$[ $MAXSLEEP - $SLEEPSTEP ]
			PIDSTATUS=`ps -aux | grep [o]ps_linking.php | awk '{print $2}' | grep -v '^${PID}$'`
			if [ "" == "$PIDSTATUS" ]; then
				echo "PIDSTATUS empty, restarting!"
				break
			else
				echo "PIDSTATUS=$PIDSTATUS, waiting empty"
			fi
		done
	else
		echo "PID not exist, restart"
	fi
else
	echo "PIDFILE not exist, restart"
fi
echo "Starting new PID..."
php /var/www/vizplus/info.viz.plus/module/ops_linking.php > ~/logs/ops_linking.log &