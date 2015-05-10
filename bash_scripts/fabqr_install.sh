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
# Argument 1: Command string
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
        if user_confirm "[INFO] Folder $1 exists, it will be deleted" "true"
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
# Argument 2: true = Quit if not confirmed, false = Continue if not confirmed
function user_confirm
{
    TIMESTRING=$( date "+%Y-%m-%d %H:%M:%S" )
    output_text_log "$1. Confirm (y/n)? "
    read -p "[$TIMESTRING] $1. Confirm (y/n)? " userinput

    case "$userinput" in
        y|Y )
            output_text "[INFO] Action confirmed"
            return 0
            ;;
        * )
            if ( $2 )
            then
                output_text "[ERROR] Action aborted by confirmation dialog, quit"
                quit_error
            else
                output_text "[ERROR] Action aborted by confirmation dialog, continue"
                return 1
            fi
            ;;
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

    return 0
}

# Function to check if a package is installed and install if it is missing
# Argument 1: Package name
function check_package_install
{
    if ! ( is_package_installed "$1" )
    then
        if user_confirm "[INFO] Package $1 needs to be installed" "true"
        then
            command_success "apt-get install $1"
        fi
    fi

    return 0
}

# Function to check if a package is installed and install if it is missing
# Search for file in different directories, if nothing found, direct download from github
# Argument 1: Directory name
# Argument 2: Target directory
# Argument 3: File name
# Argument 4: true = Set default file properties, false = Do not set default file properties
function get_fabqr_file
{
    # Check if target directory exists
    if ! [ -d "$2" ]
    then
        output_text "[INFO] Target directory $2 for FabQR file $1/$3 does not exist"
        output_text "[INFO] Create directory $2"
        command_success "mkdir $2"

        if ( $4 )
        then
            output_text "[INFO] Set default properties for directory $2"
            command_success "chown fabqr $2 -R"
            command_success "chgrp fabqr $2 -R"
            command_success "chmod 770 $2 -R"
        fi
    fi

    # Check if file already exists and is not empty (which could happen because of error in wget)
    if [ -e "$2/$3" ] && [ -s "$2/$3" ]
    then
        # Set default file properties if argument 4 is true
        if ( $4 )
        then
            file_properties "$2/$3" "fabqr" "fabqr" "-rwxrwx---" "770"
        fi

        return 1
    fi

    # Local folder
    if [ -e "$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success "mv $3 $2/$3"
        return 0
    fi

    # Local folder, subfolder
    if [ -e "$1/$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success "mv $1/$3 $2/$3"
        return 0
    fi

    # Parent folder
    if [ -e "../$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success "mv ../$3 $2/$3"
        return 0
    fi

    # Parent folder, subfolder
    if [ -e "../$1/$3" ]
    then
        output_text "[INFO] Moving FabQR file $1/$3 to target $2/$3"
        command_success "mv ../$1/$3 $2/$3"
        return 0
    fi

    # Download from github
    output_text "[INFO] FabQR file $1/$3 not found in local file system, downloading to target $2/$3"
    command_success "wget -q -O $2/$3 https://raw.githubusercontent.com/FroChr123/FabQR/master/$1/$3"

    # Set default file properties if argument 4 is true
    if ( $4 )
    then
        file_properties "$2/$3" "fabqr" "fabqr" "-rwxrwx---" "770"
    fi

    return 0
}

# Function to check and change owner, group and permissions if neccessary
# Argument 1: File path
# Argument 2: Owner name
# Argument 3: Group name
# Argument 4: Permission string
# Argument 5: Permission bitmask
function file_properties
{
    # Check owner
    if [ $( stat -c %U $1 ) != "$2" ]
    then
        output_text "[INFO] Owner of file $1 was incorrect, set to $2"
        command_success "chown $2 $1"
    fi

    # Check group
    if [ $( stat -c %G $1 ) != "$3" ]
    then
        output_text "[INFO] Group of file $1 was incorrect, set to $3"
        command_success "chgrp $3 $1"
    fi

    # Check permission
    if [ $( stat -c %A $1 ) != "$4" ]
    then
        output_text "[INFO] Permissions of file $1 were incorrect, set to $5"
        command_success "chmod $5 $1"
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
# STOP FABQR
# ##################################################################

if [ -e "/etc/init.d/fabqr_service" ] && [ -e "/home/fabqr/fabqr_stop.sh" ]
then
    command_success "service fabqr_service stop"
fi

# ##################################################################
# PACKAGES
# ##################################################################

output_text "[INFO] Checking required packges"
output_text "[INFO] Updating package lists"

command_success "apt-get update"

# Tools for script and system
check_package_install "grep"
check_package_install "sed"
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

# Check if user www-data exists
if ! ( getent passwd www-data > /dev/null )
then
    output_text "[ERROR] apache2 is installed, but user www-data does not exist!"
    quit_error
fi

# Check if group www-data exists
if ! ( getent group www-data > /dev/null )
then
    output_text "[ERROR] apache2 is installed, but group www-data does not exist!"
    quit_error
fi

check_package_install "php5"
check_package_install "php5-cli"
check_package_install "php5-common"
check_package_install "libapache2-mod-php5"
check_package_install "php5-gd"
check_package_install "g++"
check_package_install "libpng12-dev"

output_text "[INFO] Required packges checked successfully"

# ##################################################################
# USER SETTINGS
# ##################################################################

# Check user settings, ensure that users are configured correctly
output_text "[INFO] Checking user settings"

# Does user fabqr exist?
if ( getent passwd fabqr > /dev/null )
then
    # User exists, correct home path?
    if ! ( ( getent passwd fabqr | grep /home/fabqr ) > /dev/null )
    then
        remove_existing_folder_confirm "/home/fabqr"

        if user_confirm "[INFO] User fabqr home path needs to be changed, contents of old home path are moved" "true"
        then
            command_success "usermod --home /home/fabqr --move-home --shell /bin/bash fabqr"
        fi
    fi

    # Check if group exists
    if ! ( getent group fabqr > /dev/null )
    then
        output_text "[INFO] Creating missing group fabqr"
        command_success "groupadd fabqr"
    fi

    # Check if user fabqr is in group fabqr
    if ! ( ( ( groups fabqr | awk -F ' : ' '{print $2}' ) | grep fabqr ) > /dev/null )
    then
        output_text "[INFO] Setting primary group of user fabqr to group fabqr"
        command_success "usermod -g fabqr fabqr"
    fi

    # Does home path exist?
    if ! [ -d "/home/fabqr" ]
    then
        output_text "[INFO] Creating missing folder /home/fabqr with correct settings"
        command_success "mkdir /home/fabqr"
        command_success "chown fabqr /home/fabqr -R"
        command_success "chgrp fabqr /home/fabqr -R"
        command_success "chmod 770 /home/fabqr -R"
    fi

    # Check owner
    if [ $( stat -c %U /home/fabqr ) != "fabqr" ]
    then
        if user_confirm "[INFO] Owner of /home/fabqr needs to be reset recursively" "true"
        then
            command_success "chown fabqr /home/fabqr -R"
        fi
    fi

    # Check group
    if [ $( stat -c %G /home/fabqr ) != "fabqr" ]
    then
        if user_confirm "[INFO] Group of /home/fabqr needs to be reset recursively" "true"
        then
            command_success "chgrp fabqr /home/fabqr -R"
        fi
    fi

    # Check permissions
    if [ $( stat -c %A /home/fabqr ) != "drwxrwx---" ]
    then
        if user_confirm "[INFO] Permissions of /home/fabqr need to be reset to 770 recursively" "true"
        then
            command_success "chmod 770 /home/fabqr -R"
        fi
    fi

else
    # Remove user home folder with confirmation, if it already exists
    remove_existing_folder_confirm "/home/fabqr"

    # Create fabqr user and set password
    output_text "[INFO] Adding user fabqr with home /home/fabqr and shell /bin/bash"
    command_success "useradd --home /home/fabqr --create-home --shell /bin/bash --user-group fabqr"
    command_success "chown fabqr /home/fabqr -R"
    command_success "chgrp fabqr /home/fabqr -R"
    command_success "chmod 770 /home/fabqr -R"
    output_text "[INFO] Remember the password for your fabqr user!"
    output_text "[INFO] For security reasons, you might want to manually allow SSH key login only!"
    command_success "passwd fabqr"
fi

# Existance of user www-data was already checked before
# Add user www-data to group fabqr, if www-data is not in that group yet
if ! ( ( ( groups www-data | awk -F ' : ' '{print $2}' ) | grep fabqr ) > /dev/null )
then
    output_text "[INFO] Adding user www-data to additional group fabqr"
    command_success "usermod -G fabqr www-data"
fi

# Existance of group www-data was already checked before
# Add user fabqr to group www-data, if fabqr is not in that group yet
if ! ( ( ( groups fabqr | awk -F ' : ' '{print $2}' ) | grep www-data ) > /dev/null )
then
    output_text "[INFO] Adding user fabqr to additional group www-data"
    command_success "usermod -G www-data fabqr"
fi

output_text "[INFO] User settings checked successfully"

# ##################################################################
# FABQR FILES / SETTINGS
# ##################################################################

output_text "[INFO] Checking FabQR files and settings"

# Copy current log to user location, if it does not exist yet
if ! [ -e "/home/fabqr/fabqr.log" ]
then
    output_text "[INFO] Moving log to fabqr home directory"
    command_success "mv fabqr.log /home/fabqr/fabqr.log"
    file_properties "/home/fabqr/fabqr.log" "fabqr" "fabqr" "-rwxrwx---" "770"
fi

# Copy current script to user location, if it does not exist yet
if ! [ -e "/home/fabqr/fabqr_install.sh" ]
then
    output_text "[INFO] Copying install script to fabqr home directory"
    command_success "cp $( dirname "$0" )/$( basename "$0" ) /home/fabqr/fabqr_install.sh"
    file_properties "/home/fabqr/fabqr_install.sh" "fabqr" "fabqr" "-rwxrwx---" "770"
fi

# FabQR start : Get file
get_fabqr_file "bash_scripts" "/home/fabqr" "fabqr_start.sh" "true"

# FabQR stop : Get file
get_fabqr_file "bash_scripts" "/home/fabqr" "fabqr_stop.sh" "true"

# FabQR service : Get file
get_fabqr_file "bash_scripts" "/etc/init.d" "fabqr_service" "false"
file_properties "/etc/init.d/fabqr_service" "root" "root" "-rwxr-xr-x" "755"

# FabQR service : Auto start entry for system boot, if it does not exist yet
if ! ( ( ls -l /etc/rc2.d | grep fabqr_service ) > /dev/null )
then
    output_text "[INFO] Creating auto start entry for fabqr_service"
    command_success "update-rc.d fabqr_service defaults > /dev/null"
fi

# crontab : Get file
get_fabqr_file "bash_scripts" "/home/fabqr" "fabqr_cron_log.sh" "true"

# crontab : User does not have crontab or fabqr_cron_log.sh is not in crontab yet
if ! ( ( crontab -u fabqr -l | grep fabqr_cron_log.sh ) &> /dev/null )
then
    # crontab : If user already has crontab, need to save it
    if ( crontab -u fabqr -l &> /dev/null )
    then
       command_success "crontab -u fabqr -l > /home/fabqr/fabqr_crontab.tmp"

        if ! [ -e "/home/fabqr/fabqr_crontab.bak" ]
        then
            command_success "crontab -u fabqr -l > /home/fabqr/fabqr_crontab.bak"
            file_properties "/home/fabqr/fabqr_crontab.bak" "fabqr" "fabqr" "-rwxrwx---" "770"
        fi
    fi

    # crontab: Write new command to crontab file
    command_success "echo >> /home/fabqr/fabqr_crontab"
    command_success "echo '# FabQR log script every 5 minutes' >> /home/fabqr/fabqr_crontab.tmp"
    command_success "echo '*/5 * * * * /home/fabqr/fabqr_cron_log.sh' >> /home/fabqr/fabqr_crontab.tmp"

    # crontab: Load crontab file for user fabqr and remove temporary file
    command_success "chmod 777 /home/fabqr/fabqr_crontab.tmp"
    command_success "crontab -u fabqr /home/fabqr/fabqr_crontab.tmp"
    command_success "rm /home/fabqr/fabqr_crontab.tmp"
    output_text "[INFO] Crontab entry for command fabqr_cron_log.sh created"
fi

# usbmount : Set option MOUNTOPTIONS="sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007" in usbmount config
# usbmount : Every user is allowed to access new mounted devices
# usbmount : Create backup of config file
if ! [ -e "/etc/usbmount/usbmount.conf.bak" ]
then
    output_text "[INFO] Backup original config file for usbmount /etc/usbmount/usbmount.conf"
    command_success "cp /etc/usbmount/usbmount.conf /etc/usbmount/usbmount.conf.bak"
    file_properties "/etc/usbmount/usbmount.conf.bak" "root" "root" "-rw-r--r--" "644"
fi

# usbmount : Place hash in front of all MOUNTOPTIONS= lines
command_success "sed -r -i 's/^(MOUNTOPTIONS=.*)$/# \1/g' /etc/usbmount/usbmount.conf"

# usbmount : Remove hash in front of MOUNTOPTIONS="sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007"
command_success "sed -r -i 's/^# (MOUNTOPTIONS=\"sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\")$/\1/g' /etc/usbmount/usbmount.conf"

# usbmount : If file does not contain line MOUNTOPTIONS="sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007", then add it
if ! ( ( cat /etc/usbmount/usbmount.conf | grep ^MOUNTOPTIONS=\"sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\"$ ) > /dev/null )
then
    output_text "[INFO] Adding line MOUNTOPTIONS=\"sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\" to file /etc/usbmount/usbmount.conf"
    output_text "[INFO] You need to reconnect USB storage devices or reboot!"
    command_success "echo >> /etc/usbmount/usbmount.conf"
    command_success "echo '# FabQR allow access for all users in group fabqr' >> /etc/usbmount/usbmount.conf"
    command_success "echo 'MOUNTOPTIONS=\"sync,noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\"' >> /etc/usbmount/usbmount.conf"
fi

# Data directory: Enter / Check / Copy path of data dir
output_text "[INFO] Configuring FabQR data directory"
output_text "[INFO] It is advised to use /media/usb* devices"
prevdir=""
newdir=""
newdirvalid=false

# Data directory: If link exists AND points to existing location, then store old path
if [ -s /home/fabqr/fabqr_data ]
then
    prevdir=$( ls -l /home/fabqr/fabqr_data | awk '{print $11}' )

    # Data directory: Check if old data directory path begins with / and is absolute path
    # Otherwise clear prevdir and set to invalid again
    if ! ( ( echo $prevdir | grep ^/.*/$ ) > /dev/null )
    then
        prevdir=""
    fi
fi

# Data directory: Set new directory value to previous
newdir="$prevdir"

# Data directory: While loop to ask user for valid new absolute path
while ! ( $newdirvalid )
do
    read -e -p "FabQR absolute data path: " -i "$newdir" newdir

    # Data directory: Check for absolute path input
    if ( ( echo $newdir | grep ^/.*/$ ) > /dev/null )
    then

        # Data directory: If no file OR directory does exist at this path, create new directory
        if ! [ -e "$newdir" ]
        then
            output_text "[INFO] Create directory $newdir"
            command_success "mkdir $newdir"
        fi

        # Data directory: Path needs to be directory
        if [ -d "$newdir" ]
        then

            # Data directory: Copy data from previous directory, if exists
            if [ -n "$prevdir" ] && [ "$newdir" != "$prevdir" ]
            then
                # Data directory: Copy data from previous directory, if user accepts
                if user_confirm "[INFO] Optional: Copy contents from old directory $prevdir to new directory $newdir" "false"
                then
                    command_success "cp -R ${prevdir}* $newdir"
                fi
            fi

            # Data directory: Set properties for directory
            # For /media/usb* devices setting the properties from before might fail, thus unchecked
            if user_confirm "[INFO] Owner of $newdir needs to be reset to fabqr recursively" "true"
            then
                chown "fabqr" "$newdir" -R
            fi

            if user_confirm "[INFO] Group of $newdir needs to be reset to fabqr recursively" "true"
            then
                chgrp "fabqr" "$newdir" -R
            fi

            if user_confirm "[INFO] Permissions of $newdir need to be reset to 770 recursively" "true"
            then
                chmod "770" "$newdir" -R
            fi

            # Data directory: Check that folder matches expected group and permissions
            # For /media/usb* devices setting the properties from before might have no effect at all
            output_text "[INFO] Checking $newdir group and permissions"

            # Check group
            if [ "$( stat -c %G "$newdir" )" == "fabqr" ]
            then
                # Check permission
                if [ "$( stat -c %A "$newdir" )" == "drwxrwx---" ]
                then
                    output_text "[INFO] Checking of $newdir was successful"
                    newdirvalid=true
                else
                    output_text "[INFO] Permission on $newdir was incorrect, for /media/usb* maybe need to reconnect USB device?"
                fi
            else
                output_text "[INFO] Group on $newdir was incorrect, for /media/usb* maybe need to reconnect USB device?"
            fi
        else
            output_text "[INFO] Path $newdir is no directory"
        fi
    else
        output_text "[INFO] Path $newdir does not begin or end with / and is wrong absolute path"
    fi
done

# Data directory: Set symbolic link
output_text "[INFO] Update symbolic link /home/fabqr/fabqr_data to $newdir"
command_success "rm /home/fabqr/fabqr_data"
command_success "ln -s $newdir /home/fabqr/fabqr_data"

# apache2 : FabQR public config, get file
get_fabqr_file "apache_configs" "/etc/apache2/sites-available" "fabqr_apache_public" "false"
file_properties "/etc/apache2/sites-available/fabqr_apache_public" "root" "root" "-rw-r--r--" "644"

# apache2 : FabQR private config, get file
get_fabqr_file "apache_configs" "/etc/apache2/sites-available" "fabqr_apache_private" "false"
file_properties "/etc/apache2/sites-available/fabqr_apache_private" "root" "root" "-rw-r--r--" "644"

# apache2 : Warning default site enabled
if [ -e "/etc/apache2/sites-enabled/000-default" ]
then
    output_text "[INFO] Default site of apache is enabled, you might want to disable it and remove port 80 from port config"
fi

# TODO apache port conf
# File: /etc/apache2/ports.conf
# NameVirtualHost *:8081
# Listen 8081
# NameVirtualHost *:8090
# Listen 8090
# Reload config


# TODO display power down time
# File: /etc/kbd/config
# BLANK_TIME=30 => BLANK_TIME=0
# POWERDOWN_TIME=30 => POWERDOWN_TIME=0

# TODO iptables
# Add to packages, need to config to prevent attacks

output_text "[INFO] FabQR files and settings checked successfully"

# ##################################################################
# FABQR GRAPHICS
# ##################################################################

output_text "[INFO] Checking FabQR graphics"

# TODO
# Download graphics c file, compile program

output_text "[INFO] FabQR graphics checked successfully"

# ##################################################################
# EXIT AND START FABQR
# ##################################################################

# Exit correctly without errors
output_text "[INFO] QUIT FABQR INSTALLER SUCCESSFULLY"
command_success "service fabqr_service start"
exit 0