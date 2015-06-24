#!/bin/bash

#    This file is part of FabQR. (https://github.com/FroChr123/FabQR)
#
#    FabQR is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Lesser General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    FabQR is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU Lesser General Public License
#    along with FabQR.  If not, see <http://www.gnu.org/licenses/>.

# ##################################################################
# FabQR cron log script
# ##################################################################

# ##################################################################
# FUNCTIONS
# ##################################################################

# Function to log a text in logfile
# Argument 1: Text
function output_text_log
{
    TIMESTRING=$( date "+%Y-%m-%d %H:%M:%S" )
    if [ -e "/home/fabqr/fabqr.log" ]
    then
        echo "[$TIMESTRING] [CRON-LOG] $1" >> "/home/fabqr/fabqr.log"
    fi
    return 0
}

# Function to check if a command exited correctly
# Argument 1: Command
function command_success
{
    if ! ( eval "$1" )
    then
        output_text "[ERROR] Error in command '$1'"
        quit_error
    fi

    return 0
}

# Function to quit correctly
function quit_error
{
    output_text_log "[ERROR] QUIT FABQR CRON LOG WITH ERRORS"
    exit 1
    return 0
}

# ##################################################################
# MAIN
# ##################################################################

# FabQR cron log start message
output_text_log "[INFO] START FABQR CRON LOG"

# ##################################################################
# CRONJOB LOG
# ##################################################################

# Truncate fabqr log size to last 2000 lines if line count exceeds 2500
if [ -e "/home/fabqr/fabqr.log" ]
then
    if [ $( cat /home/fabqr/fabqr.log | wc -l ) -gt 2500 ]
    then
        command_success "tail -n 2000 /home/fabqr/fabqr.log > /home/fabqr/tmp_fabqr.log"
        command_success "mv /home/fabqr/tmp_fabqr.log /home/fabqr/fabqr.log"
        output_text_log "[INFO] Truncated fabqr log to 2000 entries"
    fi

    output_text_log "[INFO] QUIT FABQR CRON LOG SUCCESSFULLY"
fi

# Truncate apache private access log size to last 200000 lines if line count exceeds 300000
if [ -e "/home/fabqr_data/logs/fabqr_private_access.log" ]
then
    if [ $( cat /home/fabqr_data/logs/fabqr_private_access.log | wc -l ) -gt 300000 ]
    then
        command_success "tail -n 200000 /home/fabqr_data/logs/fabqr_private_access.log > /home/fabqr_data/logs/tmp_fabqr_private_access.log"
        command_success "mv /home/fabqr_data/logs/tmp_fabqr_private_access.log /home/fabqr_data/logs/fabqr_private_access.log"
        output_text_log "[INFO] Truncated apache private access log to 200000 entries"
    fi
fi

# Truncate apache private error log size to last 200000 lines if line count exceeds 300000
if [ -e "/home/fabqr_data/logs/fabqr_private_error.log" ]
then
    if [ $( cat /home/fabqr_data/logs/fabqr_private_error.log | wc -l ) -gt 300000 ]
    then
        command_success "tail -n 200000 /home/fabqr_data/logs/fabqr_private_error.log > /home/fabqr_data/logs/tmp_fabqr_private_error.log"
        command_success "mv /home/fabqr_data/logs/tmp_fabqr_private_error.log /home/fabqr_data/logs/fabqr_private_error.log"
        output_text_log "[INFO] Truncated apache private error log to 200000 entries"
    fi
fi

# Truncate apache public access log size to last 200000 lines if line count exceeds 300000
if [ -e "/home/fabqr_data/logs/fabqr_public_access.log" ]
then
    if [ $( cat /home/fabqr_data/logs/fabqr_public_access.log | wc -l ) -gt 300000 ]
    then
        command_success "tail -n 200000 /home/fabqr_data/logs/fabqr_public_access.log > /home/fabqr_data/logs/tmp_fabqr_public_access.log"
        command_success "mv /home/fabqr_data/logs/tmp_fabqr_public_access.log /home/fabqr_data/logs/fabqr_public_access.log"
        output_text_log "[INFO] Truncated apache public access log to 200000 entries"
    fi
fi

output_text_log "[INFO] QUIT FABQR CRON SUCCESSFULLY"
exit 0