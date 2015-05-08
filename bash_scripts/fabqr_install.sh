#!/bin/bash

#    This file is part of FabQR. (https://github.com/FroChr123/FabQR)
#
#    FabQR is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    FabQR is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with FabQR.  If not, see <http://www.gnu.org/licenses/>.

# ##################################################################
# FabQR installer script
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
        echo "[$TIMESTRING] [INSTALLER] $1" >> "/home/fabqr/fabqr.log"
    else
        echo "[$TIMESTRING] [INSTALLER] $1" >> "fabqr.log"
    fi
    return 0
}

# Function to check if a command exited correctly
# Argument 1: Command
function command_success
{
    if ! ( $1 )
    then
        output_text "[ERROR] Error in command '$1'"
        quit_error
    fi

    return 0
}

# Function to quit correctly
function quit_error
{
    output_text "[ERROR] QUIT FABQR INSTALLER WITH ERRORS"
    exit 1
    return 0
}

# Function to remove folder with user confirmation
# Argument 1: Absolute path to folder
function remove_existing_folder_confirm
{
    if [ -d "$1" ]
    then
        if user_confirm "[INFO] Folder $1 exists, it will be deleted"
        then
            if rm -R "$1"
            then
                output_text "[INFO] Folder $1 successfully deleted"
            else
                output_text "[ERROR] Error in deleting folder $1"
                quit_error
            fi
        fi
    fi
    return 0
}

# Function to prompt for user input yes or no
# Argument 1: Text
function user_confirm
{
    TIMESTRING=$( date "+%Y-%m-%d %H:%M:%S" )
    output_text_log "$1. Confirm (y/n)? "
    read -p "[$TIMESTRING] $1. Confirm (y/n)? " userinput
    case "$userinput" in
        y|Y ) output_text "[INFO] Action confirmed"; return 0;;
        * ) output_text "[ERROR] Action aborted by confirmation dialog"; quit_error;;
    esac
    return 0
}

# Function to check if a package is installed
# Argument 1: Package name
function is_package_installed
{
    if ! ( dpkg -s "$1" &> /dev/null )
    then
        output_text "[INFO] Package $1 is not installed"
        return 1
    fi

    output_text "[INFO] Package $1 is already installed"
    return 0
}

# Function to check if a package is installed and install if it is missing
# Argument 1: Package name
function check_package_install
{
    if ! ( is_package_installed "$1" )
    then
        if user_confirm "[INFO] Package $1 needs to be installed"
        then
            command_success `apt-get install $1`
        fi
    fi

    return 0
}

# Function to check if a package is installed and install if it is missing
# Search for file in different directories, if nothing found, direct download from github
# Argument 1: Directory name
# Argument 2: Target directory
# Argument 3: File name
function get_fabqr_file
{
    # Check if file already exists
    if [ -e "$2/$3" ]
    then
        output_text "[INFO] FabQR file $1/$3 already exists at target $2/$3"
        file_defaults "$2/$3"
        return 1
    fi

    # Local folder
    if [ -e "$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success `mv $3 $2/$3`
        return 0
    fi

    # Local folder, subfolder
    if [ -e "$1/$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success `mv $1/$3 $2/$3`
        return 0
    fi

    # Parent folder
    if [ -e "../$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success `mv ../$3 $2/$3`
        return 0
    fi

    # Parent folder, subfolder
    if [ -e "../$1/$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success `mv ../$1/$3 $2/$3`
        return 0
    fi

    # Download from github
    output_text "[INFO] FabQR file $1/$3 not found in local file system, downloading to target $2/$3"
    command_success `wget -O $2/$3 https://raw.githubusercontent.com/FroChr123/FabQR/master/$1/$3`
    file_defaults "$2/$3"
    return 0
}

# Function to check and change owner, group and permissions if neccessary
# Argument 1: File path
function file_defaults
{
    # Check owner
    if [ $( stat -c %U $1 ) != "fabqr" ]
    then
        output_text "[INFO] Owner of file $1 was incorrect, set to default fabqr"
        command_success `chown fabqr $1`
    fi

    # Check group
    if [ $( stat -c %G $1 ) != "fabqr" ]
    then
        output_text "[INFO] Group of file $1 was incorrect, set to default fabqr"
        command_success `chgrp fabqr $1`
    fi

    # Check permission
    if [ $( stat -c %A $1 ) != "-rwxrwx---" ]
    then
        output_text "[INFO] Permissions of file $1 were incorrect, set to default 770"
        command_success `chmod 770 $1`
    fi

    return 0
}

# ##################################################################
# MAIN
# ##################################################################

# FabQR installer start message
output_text "[INFO] START FABQR INSTALLER"
output_text "[INFO] You can re-run this script to reconfigure your FabQR system"

# ##################################################################
# ROOT
# ##################################################################

# Check if script is executed as root, if not, quit with error message
if [[ $EUID -ne 0 ]]
then
    output_text "[ERROR] FabQR installer must be started as root, try sudo command"
    quit_error
fi

# ##################################################################
# PACKAGES
# ##################################################################

output_text "[INFO] Checking required packges"
output_text "[INFO] Updating package lists"

command_success `apt-get update`

# Tools for script and system
check_package_install "wget"
check_package_install "mawk"
check_package_install "cron"
check_package_install "usbmount"

# Check if usbmount.conf is in correct directory
if ! [ -e "/etc/usbmount/usbmount.conf" ]
then
    output_text "[ERROR] usbmount is installed, but file /etc/usbmount/usbmount.conf does not exist!"
    quit_error
fi

# Software for FabQR
check_package_install "apache2"

# Check if apache directory is correct
if ! [ -d "/etc/apache2/sites-available" ]
then
    output_text "[ERROR] apache2 is installed, but directory /etc/apache2/sites-available does not exist!"
    quit_error
fi

check_package_install "php5"
check_package_install "php5-cli"
check_package_install "php5-common"
check_package_install "libapache2-mod-php5"
check_package_install "php5-gd"
check_package_install "g++"

# ##################################################################
# FABQR USER
# ##################################################################

# Check fabqr user, ensure that user is configured correctly
output_text "[INFO] Checking user fabqr"

# Does user fabqr exist?
if ( getent passwd fabqr > /dev/null )
then
    # User exists, correct home path?
    if ! ( ( getent passwd fabqr | grep /home/fabqr ) > /dev/null )
    then
        remove_existing_folder_confirm "/home/fabqr"

        if user_confirm "[INFO] User fabqr home path needs to be changed, contents of old home path are moved"
        then
            command_success `usermod --home /home/fabqr --move-home --shell /bin/bash fabqr`
        fi
    fi

    # Check if group exists
    if ! ( getent group fabqr > /dev/null )
    then
        output_text "[INFO] Creating missing group fabqr"
        command_success `groupadd fabqr`
    fi

    # Check if user fabqr is in group fabqr
    if ! ( ( ( groups fabqr | awk -F ' : ' '{print $2}' ) | grep fabqr ) > /dev/null )
    then
        output_text "[INFO] Adding user fabqr to group fabqr"
        command_success `usermod -g fabqr fabqr`
    fi

    # Does home path exist?
    if ! [ -d "/home/fabqr" ]
    then
        output_text "[INFO] Creating missing folder /home/fabqr with correct settings"
        command_success `mkdir /home/fabqr`
        command_success `chown fabqr /home/fabqr -R`
        command_success `chgrp fabqr /home/fabqr -R`
        command_success `chmod 770 /home/fabqr -R`
    fi

    # Check owner
    if [ $( stat -c %U /home/fabqr ) != "fabqr" ]
    then
        if user_confirm "[INFO] Owner of /home/fabqr needs to be reset recursively"
        then
            command_success `chown fabqr /home/fabqr -R`
        fi
    fi

    # Check group
    if [ $( stat -c %G /home/fabqr ) != "fabqr" ]
    then
        if user_confirm "[INFO] Group of /home/fabqr needs to be reset recursively"
        then
            command_success `chgrp fabqr /home/fabqr -R`
        fi
    fi

    # Check permissions
    if [ $( stat -c %A /home/fabqr ) != "drwxrwx---" ]
    then
        if user_confirm "[INFO] Permissions of /home/fabqr need to be reset to 770 recursively"
        then
            command_success `chmod 770 /home/fabqr -R`
        fi
    fi

else
    # Remove user home folder with confirmation, if it already exists
    remove_existing_folder_confirm "/home/fabqr"

    # Create fabqr user and set password
    output_text "[INFO] Adding user fabqr with home /home/fabqr and shell /bin/bash"
    command_success `useradd --home /home/fabqr --create-home --shell /bin/bash --user-group fabqr`
    command_success `chown fabqr /home/fabqr/fabqr_install.sh`
    command_success `chgrp fabqr /home/fabqr/fabqr_install.sh`
    command_success `chmod 770 /home/fabqr/fabqr_install.sh`
    output_text "[INFO] Remember the password for your fabqr user!"
    output_text "[INFO] For security reasons, you might want to manually allow SSH key login only!"
    command_success `passwd fabqr`
fi

output_text "[INFO] User fabqr checked successfully"

# ##################################################################
# FABQR FILES / SETTINGS
# ##################################################################

# Copy current log to user location, if it does not exist yet
if ! [ -e "/home/fabqr/fabqr.log" ]
then
    output_text "[INFO] Moving log to fabqr home directory"
    command_success `mv fabqr.log /home/fabqr/fabqr.log`
    command_success `chown fabqr /home/fabqr/fabqr.log`
    command_success `chgrp fabqr /home/fabqr/fabqr.log`
    command_success `chmod 770 /home/fabqr/fabqr.log`
fi

# Copy current script to user location, if it does not exist yet
if ! [ -e "/home/fabqr/fabqr_install.sh" ]
then
    output_text "[INFO] Copying install script to fabqr home directory"
    command_success `cp $( dirname "$0" )/$( basename "$0" ) /home/fabqr/fabqr_install.sh`
    command_success `chown fabqr /home/fabqr/fabqr_install.sh`
    command_success `chgrp fabqr /home/fabqr/fabqr_install.sh`
    command_success `chmod 770 /home/fabqr/fabqr_install.sh`
fi

# Crontab : Get file
get_fabqr_file "bash_scripts" "/home/fabqr" "fabqr_cron_log.sh"

# Crontab : User does not have crontab or fabqr_cron_log.sh is not in crontab yet
if ! ( ( crontab -u fabqr -l | grep fabqr_cron_log.sh ) &> /dev/null )
then
    # Crontab : If user already has crontab, need to save it
    if ( crontab -u fabqr -l &> /dev/null )
    then
       command_success `crontab -u fabqr -l > /home/fabqr/fabqr_crontab`
    fi

    # Crontab: Write new command to crontab file
    command_success `echo >> /home/fabqr/fabqr_crontab`
    command_success `echo "# FabQR log script every 5 minutes" >> /home/fabqr/fabqr_crontab`
    command_success `echo "*/5 * * * * /home/fabqr/fabqr_cron_log.sh" >> /home/fabqr/fabqr_crontab`

    # Crontab: Load crontab file for user fabqr and remove temporary file
    command_success `chmod 777 /home/fabqr/fabqr_crontab`
    command_success `crontab -u fabqr /home/fabqr/fabqr_crontab`
    command_success `rm /home/fabqr/fabqr_crontab`
    output_text "[INFO] Crontab entry for command fabqr_cron_log.sh created"
else
    output_text "[INFO] Crontab entry for command fabqr_cron_log.sh is already correct"
fi

# Exit correctly without errors
output_text "[INFO] QUIT FABQR INSTALLER SUCCESSFULLY"
exit 0