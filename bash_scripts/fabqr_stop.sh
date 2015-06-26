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
# FabQR stop script
# ##################################################################

# ##################################################################
# FUNCTIONS
# ##################################################################

# Function to show a text in console and to log it in logfile
# Argument 1: Text
function output_text
{
    output_text_std "$1"
    output_text_log "$1"
    return 0
}

# Function to show a text in console
# Argument 1: Text
function output_text_std
{
    TIMESTRING=$( date "+%Y-%m-%d %H:%M:%S" )
    echo "[$TIMESTRING] $1"
    return 0
}

# Function to log a text in logfile
# Argument 1: Text
function output_text_log
{
    TIMESTRING=$( date "+%Y-%m-%d %H:%M:%S" )
    if [ -e "/home/fabqr/fabqr.log" ]
    then
        echo "[$TIMESTRING] [STOP] $1" >> "/home/fabqr/fabqr.log"
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
    output_text_log "[ERROR] QUIT FABQR STOP SCRIPT WITH ERRORS"
    exit 1
    return 0
}

# ##################################################################
# MAIN
# ##################################################################

# FabQR stop script start message
output_text "[INFO] START FABQR STOP SCRIPT"

# ##################################################################
# ROOT
# ##################################################################

# Check if script is executed as root, if not, quit with error message
if [[ $EUID -ne 0 ]]
then
    output_text "[ERROR] FabQR stop script must be started as root, try sudo command"
    quit_error
fi

# ##################################################################
# APACHE
# ##################################################################

if [ -e "/etc/apache2/sites-enabled/fabqr_apache_public" ]
then
    command_success "a2dissite fabqr_apache_public"
fi

if [ -e "/etc/apache2/sites-enabled/fabqr_apache_private" ]
then
    command_success "a2dissite fabqr_apache_private"
fi

if [ -e "/etc/apache2/sites-enabled/fabqr_apache_both" ]
then
    command_success "a2dissite fabqr_apache_both"
fi

# ##################################################################
# GRAPHICS
# ##################################################################

command_success "echo 1 > /sys/class/graphics/fbcon/cursor_blink"
screen -S fabqr_framebuffer_png -X quit &> "/dev/null"
output_text "[INFO] QUIT FABQR STOP SCRIPT SUCCESSFULLY"
exit 0