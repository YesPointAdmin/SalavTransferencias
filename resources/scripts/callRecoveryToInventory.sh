#!/bin/bash
set -euo pipefail

function try()
{
    [[ $- = *e* ]]; SAVED_OPT_E=$?
    set +e
}

function throw()
{
    exit $1
}

function catch()
{
    export exception_code=$?
    (( $SAVED_OPT_E )) && set +e
    return $exception_code
}

export ERR_BAD=100
export ERR_WORSE=101
export ERR_CRITICAL=102

try
(
    echo "Init process to recovery data into inventory...";
    declare +i -r ACTUAL_DATE_FORMAT=$(date +%Y%m%d) || throw $ERR_BAD; 
    declare +i -r LOGGER_BASE_DIR="/opt/lampp//htdocs/SalavRefaccion/SalavTransferencias/logs/general/recovery" || throw $ERR_BAD;
    declare +i -r LOGGER_PATH="$LOGGER_BASE_DIR/data_recovery_$ACTUAL_DATE_FORMAT" || throw $ERR_BAD;
    echo "Gonna make a log at: $LOGGER_PATH";
    if [[ ! -e $LOGGER_PATH ]]; then
		echo "Gonna create log file...";
        sudo -u daemon -g daemon mkdir -p $LOGGER_BASE_DIR || throw $ERR_WORSE;
        sudo -u daemon -g daemon touch $LOGGER_PATH || throw $ERR_WORSE;
        chmod 666 $LOGGER_PATH || throw $ERR_WORSE;
    fi

    declare +i -r LOGGER=logger || throw $ERR_BAD;
    declare +i -r LOGGER_OPTS="-f $LOGGER_PATH" || throw $ERR_BAD;
    echo "Options Log: $LOGGER_PATH";


    declare +i -r BASE_DIRECTORY_SR="/opt/lampp//htdocs/SalavRefaccion" || throw $ERR_BAD;
    declare +i -r BASE_FILE_RECOVERY="/SalavTransferencias/resources/scripts" || throw $ERR_BAD;
    declare +i -r RECOVERY_FILE="/recoveryDataInventory.php" || throw $ERR_BAD;
    echo "Script to run at: $BASE_DIRECTORY_SR$BASE_FILE_RECOVERY$RECOVERY_FILE";

    PHP=`which php` || throw $ERR_BAD;
    echo "Php bin: $PHP";

    $PHP $BASE_DIRECTORY_SR$BASE_FILE_RECOVERY$RECOVERY_FILE || throw $ERR_CRITICAL;
    echo "End of process...";
)
catch || {
    case $exception_code in
        $ERR_BAD)
            echo "This error is bad"
        ;;
        $ERR_WORSE)
            echo "This error is worse"
        ;;
        $ERR_CRITICAL)
            echo "This error is critical"
        ;;
        *)
            echo "Unknown error: $exit_code"
            throw $exit_code    # re-throw an unhandled exception
        ;;
    esac
}