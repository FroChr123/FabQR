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
# FabQR start script
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
        echo "[$TIMESTRING] [START] $1" >> "/home/fabqr/fabqr.log"
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
    output_text_log "[ERROR] QUIT FABQR START SCRIPT WITH ERRORS"
    exit 1
    return 0
}

# ##################################################################
# MAIN
# ##################################################################

# FabQR start script start message
output_text "[INFO] START FABQR START SCRIPT"

# ##################################################################
# ROOT
# ##################################################################

# Check if script is executed as root, if not, quit with error message
if [[ $EUID -ne 0 ]]
then
    output_text "[ERROR] FabQR start script must be started as root, try sudo command"
    quit_error
fi

# ##################################################################
# STOP FABQR
# ##################################################################
command_success "service fabqr stop"

# ##################################################################
# APACHE
# ##################################################################

if [ -e "/etc/apache2/sites-available/fabqr_apache_public" ] && ! [ -e "/etc/apache2/sites-enabled/fabqr_apache_public" ]
then
    command_success "a2ensite fabqr_apache_public"
fi

if [ -e "/etc/apache2/sites-available/fabqr_apache_private" ] && ! [ -e "/etc/apache2/sites-enabled/fabqr_apache_private" ]
then
    command_success "a2ensite fabqr_apache_private"
fi

if [ -e "/etc/apache2/sites-available/fabqr_apache_both" ] && ! [ -e "/etc/apache2/sites-enabled/fabqr_apache_both" ]
then
    command_success "a2ensite fabqr_apache_both"
fi

output_text "[INFO] Reloading apache2 configuration"
command_success "service apache2 reload"

# ##################################################################
# GRAPHICS
# ##################################################################

# Check if graphics are activated
if [ -e "/dev/fb0" ]
then
    if [ -e "/home/fabqr/fabqr_framebuffer_png_width" ] && [ -e "/home/fabqr/fabqr_framebuffer_png_height" ]
    then
        command_success "echo 0 > /sys/class/graphics/fbcon/cursor_blink"

        # Read values from files
        width=$( head -n 1 /home/fabqr/fabqr_framebuffer_png_width )
        height=$( head -n 1 /home/fabqr/fabqr_framebuffer_png_height )

        # Set resolution to screen
        command_success "fbset -g $width $height $width $height 16"

        # Start program
        command_success "screen -dmS fabqr_framebuffer_png /home/fabqr/fabqr_framebuffer_png /dev/fb0 /run/shm/pngdisplay.png"
    fi
fi

output_text "[INFO] QUIT FABQR START SCRIPT SUCCESSFULLY"
exit 0