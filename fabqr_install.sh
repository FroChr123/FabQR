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
                output_text "[INFO] Action aborted by confirmation dialog, continue"
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
    if ! ( dpkg -s "$1" &> "/dev/null" )
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

# Function to copy files to correct directories
# Argument 1: Path relative to repository root
# Argument 2: Target file path
function copy_fabqr_file
{
    # File must be in local folder, subfolder
    if [ -e "/home/fabqr/fabqr_repository/$1" ]
    then
        output_text "[INFO] Copying FabQR file $1 to target $2"
        command_success "cp /home/fabqr/fabqr_repository/$1 $2"
        return 0
    else
        output_text "[ERROR] File /home/fabqr/fabqr_repository/$1 not found, quit"
        quit_error
    fi

    file_properties "$2" "fabqr" "fabqr" "-rwxrwx---" "770" "false"

    return 0
}

# Function to check and change owner, group and permissions if neccessary
# Argument 1: File path
# Argument 2: Owner name
# Argument 3: Group name
# Argument 4: Permission string
# Argument 5: Permission bitmask
# Argument 6: true = Ignore setting of owner and do not force check, false = default behavior
function file_properties
{
    # Check owner
    if ! ( $6 )
    then
        if [ $( stat -c %U $1 ) != "$2" ]
        then
            output_text "[INFO] Owner of file $1 was incorrect, set to $2"
            command_success "chown $2 $1"
        fi
    fi

    # Check group
    if [ $( stat -c %G $1 ) != "$3" ]
    then
        output_text "[INFO] Group of file $1 was incorrect, set to $3"

        if ! ( $6 )
        then
            command_success "chgrp $3 $1"
        else
            chgrp "$3" "$1"
        fi
    fi

    # Check permission
    if [ $( stat -c %A $1 ) != "$4" ]
    then
        output_text "[INFO] Permissions of file $1 were incorrect, set to $5"

        if ! ( $6 )
        then
            command_success "chmod $5 $1"
        else
            chgrp "chmod $5 $1"
        fi
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

if [ -e "/etc/init.d/fabqr" ] && [ -e "/home/fabqr/fabqr_stop.sh" ]
then
    command_success "service fabqr stop"
fi

# ##################################################################
# SECURITY INFORMATION
# ##################################################################

output_text "[INFO] SECURITY: fail2ban and iptables provide a basic security"
output_text "[INFO] SECURITY: You should MANUALLY setup a good networking and authentication:"
output_text "[INFO] SECURITY: E.g.: Disable remote root login and only allow logins with SSH keys"

# ##################################################################
# REDOWNLOAD
# ##################################################################

redownload=false

if user_confirm "[INFO] Optional: Force redownload of all FabQR files" "false"
then
    redownload=true
fi

# ##################################################################
# REBOOT VARIABLE
# ##################################################################

reboot=false

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
check_package_install "git"

# Check system paths
if ! [ -d "/usr/local/include" ]
then
    output_text "[ERROR] System directory /usr/local/include does not exist!"
    quit_error
fi

if ! [ -d "/usr/local/lib" ]
then
    output_text "[ERROR] System directory /usr/local/lib does not exist!"
    quit_error
fi

# Shared memory: Check if shared memory folder exists
if ! [ -d "/run/shm" ]
then
    output_text "[ERROR] There is no shared memory at /run/shm"
    quit_error
fi

# Network security
check_package_install "iptables"
check_package_install "fail2ban"

# Check if jail.conf is in correct directory
if ! [ -e "/etc/fail2ban/jail.conf" ]
then
    output_text "[ERROR] fail2ban is installed, but file /etc/fail2ban/jail.conf does not exist!"
    quit_error
fi

# Check if fail2ban directories are correct
if ! [ -d "/etc/fail2ban/filter.d" ]
then
    output_text "[ERROR] fail2ban is installed, but directory /etc/fail2ban/filter.d does not exist!"
    quit_error
fi

# Auto mount usb devices
check_package_install "usbmount"

# Check if usbmount.conf is in correct directory
if ! [ -e "/etc/usbmount/usbmount.conf" ]
then
    output_text "[ERROR] usbmount is installed, but file /etc/usbmount/usbmount.conf does not exist!"
    quit_error
fi

# Software for FabQR
check_package_install "apache2"
check_package_install "apache2-utils"

# Check if apache directories are correct
if ! [ -d "/etc/apache2/sites-available" ]
then
    output_text "[ERROR] apache2 is installed, but directory /etc/apache2/sites-available does not exist!"
    quit_error
fi

if ! [ -d "/etc/apache2/sites-enabled" ]
then
    output_text "[ERROR] apache2 is installed, but directory /etc/apache2/sites-enabled does not exist!"
    quit_error
fi

if ! [ -d "/etc/apache2/conf.d" ]
then
    output_text "[ERROR] apache2 is installed, but directory /etc/apache2/conf.d does not exist!"
    quit_error
fi

# Check if apache port config is correct
if ! [ -e "/etc/apache2/ports.conf" ]
then
    output_text "[ERROR] apache2 is installed, but file /etc/apache2/ports.conf does not exist!"
    quit_error
fi

# Check if user www-data exists
if ! ( getent passwd www-data > "/dev/null" )
then
    output_text "[ERROR] apache2 is installed, but user www-data does not exist!"
    quit_error
fi

# Check if group www-data exists
if ! ( getent group www-data > "/dev/null" )
then
    output_text "[ERROR] apache2 is installed, but group www-data does not exist!"
    quit_error
fi

check_package_install "php5"
check_package_install "php5-cli"
check_package_install "php5-common"
check_package_install "libapache2-mod-php5"
check_package_install "php5-gd"

# Check PHP config directory
if ! [ -d "/etc/php5/conf.d" ]
then
    output_text "[ERROR] PHP5 is installed, but directory /etc/php5/conf.d does not exist!"
    quit_error
fi

check_package_install "build-essential"

output_text "[INFO] Required packges checked successfully"

# ##################################################################
# USER SETTINGS
# ##################################################################

# Check user settings, ensure that users are configured correctly
output_text "[INFO] Checking user settings"

# Does user fabqr exist?
if ( getent passwd fabqr > "/dev/null" )
then
    # User exists, correct home path?
    if ! ( ( getent passwd fabqr | grep :/home/fabqr: ) > "/dev/null" )
    then
        remove_existing_folder_confirm "/home/fabqr"

        if user_confirm "[INFO] User fabqr home path needs to be changed, contents of old home path are moved" "true"
        then
            command_success "usermod --home /home/fabqr --move-home --shell /bin/bash fabqr"
        fi
    fi

    # Check if group exists
    if ! ( getent group fabqr > "/dev/null" )
    then
        output_text "[INFO] Creating missing group fabqr"
        command_success "groupadd fabqr"
    fi

    # Check if user fabqr is in group fabqr
    if ! ( ( ( groups fabqr | awk -F ' : ' '{print $2}' ) | grep fabqr ) > "/dev/null" )
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
        command_success "chown fabqr /home/fabqr -R"
    fi

    # Check group
    if [ $( stat -c %G /home/fabqr ) != "fabqr" ]
    then
        command_success "chgrp fabqr /home/fabqr -R"
    fi

    # Check permissions
    if [ $( stat -c %A /home/fabqr ) != "drwxrwx---" ]
    then
        command_success "chmod 770 /home/fabqr -R"
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
if ! ( ( ( groups www-data | awk -F ' : ' '{print $2}' ) | grep fabqr ) > "/dev/null" )
then
    output_text "[INFO] Adding user www-data to additional group fabqr"
    command_success "usermod -G fabqr www-data"
fi

# Existance of group www-data was already checked before
# Add user fabqr to group www-data, if fabqr is not in that group yet
if ! ( ( ( groups fabqr | awk -F ' : ' '{print $2}' ) | grep www-data ) > "/dev/null" )
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
    file_properties "/home/fabqr/fabqr.log" "fabqr" "fabqr" "-rwxrwx---" "770" "false"
fi

# Handle download of repository folder
if ( $redownload )
then
    if [ -d "/home/fabqr/fabqr_repository" ]
    then
        output_text "[INFO] Redownload active, remove existing repository folder"
        remove_existing_folder_confirm "/home/fabqr/fabqr_repository"
    fi
fi

if ! [ -d "/home/fabqr/fabqr_repository" ]
then
    output_text "[INFO] Download FabQR repository"
    command_success "git clone https://github.com/FroChr123/FabQR.git /home/fabqr/fabqr_repository"
fi

# FabQR start : Copy file
copy_fabqr_file "bash_scripts/fabqr_start.sh" "/home/fabqr/fabqr_start.sh"

# FabQR stop : Copy file
copy_fabqr_file "bash_scripts/fabqr_stop.sh" "/home/fabqr/fabqr_stop.sh"

# FabQR service : Copy file
copy_fabqr_file "bash_scripts/fabqr" "/etc/init.d/fabqr"
file_properties "/etc/init.d/fabqr" "root" "root" "-rwxr-xr-x" "755" "false"

# FabQR service : Auto start entry for system boot, if it does not exist yet
if ! ( ( ls -l "/etc/rc2.d" | grep fabqr ) > "/dev/null" )
then
    output_text "[INFO] Creating auto start entry for fabqr"
    command_success "update-rc.d fabqr defaults > /dev/null"
fi

# FabQR cron script : Copy file
copy_fabqr_file "bash_scripts/fabqr_cron.sh" "/home/fabqr/fabqr_cron.sh"

# crontab : User does not have crontab or fabqr_cron.sh is not in crontab yet
if ! ( ( crontab -u fabqr -l | grep fabqr_cron.sh ) &> "/dev/null" )
then
    # crontab : If user already has crontab, need to save it
    if ( crontab -u fabqr -l &> "/dev/null" )
    then
       command_success "crontab -u fabqr -l > /home/fabqr/fabqr_crontab.tmp"

        if ! [ -e "/home/fabqr/fabqr_crontab.bak" ]
        then
            command_success "crontab -u fabqr -l > /home/fabqr/fabqr_crontab.bak"
            file_properties "/home/fabqr/fabqr_crontab.bak" "fabqr" "fabqr" "-rwxrwx---" "770" "false"
        fi
    fi

    # crontab: Write new command to crontab file
    command_success "echo >> /home/fabqr/fabqr_crontab.tmp"
    command_success "echo '# FabQR log script every minute' >> /home/fabqr/fabqr_crontab.tmp"
    command_success "echo '*/1 * * * * /home/fabqr/fabqr_cron.sh' >> /home/fabqr/fabqr_crontab.tmp"

    # crontab: Load crontab file for user fabqr and remove temporary file
    command_success "chmod 777 /home/fabqr/fabqr_crontab.tmp"
    command_success "crontab -u fabqr /home/fabqr/fabqr_crontab.tmp"
    command_success "rm /home/fabqr/fabqr_crontab.tmp"
    output_text "[INFO] Crontab entry for command fabqr_cron.sh created"
fi

# usbmount : Set option MOUNTOPTIONS="noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007" in usbmount config
# usbmount : Every user is allowed to access new mounted devices
# usbmount : Create backup of config file
if ! [ -e "/etc/usbmount/usbmount.conf.bak" ]
then
    output_text "[INFO] Backup original config file for usbmount /etc/usbmount/usbmount.conf"
    command_success "cp /etc/usbmount/usbmount.conf /etc/usbmount/usbmount.conf.bak"
    file_properties "/etc/usbmount/usbmount.conf.bak" "root" "root" "-rw-r--r--" "644" "false"
fi

# usbmount : Place hash in front of all MOUNTOPTIONS= lines
command_success "sed -r -i 's/^(MOUNTOPTIONS=.*)$/# \1/g' /etc/usbmount/usbmount.conf"

# usbmount : Remove hash in front of MOUNTOPTIONS="noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007"
command_success "sed -r -i 's/^# (MOUNTOPTIONS=\"noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\")$/\1/g' /etc/usbmount/usbmount.conf"

# usbmount : If file does not contain line MOUNTOPTIONS="noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007", then add it
if ! ( ( cat "/etc/usbmount/usbmount.conf" | grep ^MOUNTOPTIONS=\"noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\"$ ) > "/dev/null" )
then
    output_text "[INFO] Adding line MOUNTOPTIONS=\"noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\" to file /etc/usbmount/usbmount.conf"
    output_text "[INFO] You need to reconnect USB storage devices or reboot!"
    reboot=true
    command_success "echo >> /etc/usbmount/usbmount.conf"
    command_success "echo '# FabQR allow access for all users in group fabqr' >> /etc/usbmount/usbmount.conf"
    command_success "echo 'MOUNTOPTIONS=\"noexec,nodev,noatime,nodiratime,uid=0,gid=fabqr,umask=007\"' >> /etc/usbmount/usbmount.conf"
fi

# Data directory: Enter / Check / Copy path of data dir
output_text "[INFO] Configuring FabQR data directory"
output_text "[INFO] It is advised to use /media/usb*/ devices"
prevdir=""
newdir=""
newdirvalid=false

# Data directory: If link exists AND points to existing location, then store old path
if [ -s "/home/fabqr/fabqr_data" ]
then
    prevdir=$( ls -l /home/fabqr/fabqr_data | awk '{print $11}' )

    # Data directory: Check if old data directory path begins with / and is absolute path
    # Otherwise clear prevdir and set to invalid again
    if ! ( ( echo $prevdir | grep ^/.*/$ ) > "/dev/null" )
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
    if ( ( echo $newdir | grep ^/.*/$ ) > "/dev/null" )
    then

        # Data directory: If no file OR directory does exist at this path, create new directory
        if ! [ -e "$newdir" ]
        then
            output_text "[INFO] Create directory $newdir"
            command_success "mkdir -p $newdir"
        fi

        # Data directory: Path needs to be directory
        if [ -d "$newdir" ]
        then

            # Data directory: Copy data from previous directory, if exists
            if [ -n "$prevdir" ] && [ "$newdir" != "$prevdir" ]
            then
                # Data directory: Copy data from previous directory, if user accepts
                # Copying might fail for empty folder, thus unchecked
                if user_confirm "[INFO] Optional: Copy contents from old directory $prevdir to new directory $newdir" "false"
                then
                    cp -R "${prevdir}." "$newdir"
                fi
            fi

            # Data directory: Create directories
            if ! [ -d "${newdir}www" ]
            then
                command_success "mkdir ${newdir}www"
            fi

            if ! [ -d "${newdir}www/public" ]
            then
                command_success "mkdir ${newdir}www/public"
            fi

            if ! [ -d "${newdir}www/private" ]
            then
                command_success "mkdir ${newdir}www/private"
            fi

            if ! [ -d "${newdir}www/includes" ]
            then
                command_success "mkdir ${newdir}www/includes"
            fi

            if ! [ -d "${newdir}apache_logs" ]
            then
                command_success "mkdir ${newdir}apache_logs"
            fi

            # Data directory: Set properties for directory
            # For /media/usb* devices setting the properties from before might fail, thus unchecked
            # Silent output for owner, is intended to output errors for /media/usb* devices
            chown "fabqr" "$newdir" -R &> "/dev/null"
            chgrp "fabqr" "$newdir" -R
            chmod "770" "$newdir" -R

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
                    output_text "[INFO] Permission on $newdir was incorrect, for /media/usb*/ maybe need to reconnect USB device?"
                fi
            else
                output_text "[INFO] Group on $newdir was incorrect, for /media/usb*/ maybe need to reconnect USB device?"
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

if [ -e "/home/fabqr/fabqr_data" ]
then
    command_success "rm /home/fabqr/fabqr_data"
fi

command_success "ln -s $newdir /home/fabqr/fabqr_data"

# webservices : Includes
copy_fabqr_file "webservices/includes/config.php" "/home/fabqr/fabqr_data/www/includes/config.php"
copy_fabqr_file "webservices/includes/functions.php" "/home/fabqr/fabqr_data/www/includes/functions.php"

# webservices : Private
copy_fabqr_file "webservices/private/api_pngdisplay.php" "/home/fabqr/fabqr_data/www/private/api_pngdisplay.php"
copy_fabqr_file "webservices/private/api_uploadproject.php" "/home/fabqr/fabqr_data/www/private/api_uploadproject.php"

# php5 : Config file for upload file sizes
copy_fabqr_file "php_configs/fabqr.ini" "/etc/php5/conf.d/fabqr.ini"
file_properties "/etc/php5/conf.d/fabqr.ini" "root" "root" "-rw-r--r--" "644" "false"

# apache2 : FabQR public config, get file
copy_fabqr_file "apache_configs/fabqr_apache_public" "/etc/apache2/sites-available/fabqr_apache_public"
file_properties "/etc/apache2/sites-available/fabqr_apache_public" "root" "root" "-rw-r--r--" "644" "false"

# apache2 : FabQR private config, get file
copy_fabqr_file "apache_configs/fabqr_apache_private" "/etc/apache2/sites-available/fabqr_apache_private"
file_properties "/etc/apache2/sites-available/fabqr_apache_private" "root" "root" "-rw-r--r--" "644" "false"

# apache2 : Password for private section
if ! [ -e "/home/fabqr/fabqr_data/www/private/.htpasswd" ]
then
    output_text "[INFO] File /home/fabqr/fabqr_data/www/private/.htpasswd does not exist"
    output_text "[INFO] Create fabqr user and set password for private www section"
    output_text "[INFO] Remember the www password for your fabqr user!"
    command_success "htpasswd -c /home/fabqr/fabqr_data/www/private/.htpasswd fabqr"
else
    if user_confirm "[INFO] Optional: Reset password for fabqr user in private www section" "false"
    then
        output_text "[INFO] File /home/fabqr/fabqr_data/www/private/.htpasswd does exists"
        output_text "[INFO] Overwrite password for fabqr user private www section"
        output_text "[INFO] Remember the www password for your fabqr user!"
        command_success "htpasswd -c /home/fabqr/fabqr_data/www/private/.htpasswd fabqr"
    fi
fi

# apache2 : FabQR security config, get file
copy_fabqr_file "apache_configs/security_fabqr_apache" "/etc/apache2/conf.d/security_fabqr_apache"
file_properties "/etc/apache2/conf.d/security_fabqr_apache" "root" "root" "-rw-r--r--" "644" "false"

# apache2 : Warning default site enabled
if [ -e "/etc/apache2/sites-enabled/000-default" ]
then
    output_text "[INFO] Default site of apache is enabled, you might want to disable it and remove port 80 from port config"
fi

# apache2 : Create backup of file /etc/apache2/ports.conf
if ! [ -e "/etc/apache2/ports.conf.bak" ]
then
    output_text "[INFO] Backup original port config file for apache2 /etc/apache2/ports.conf"
    command_success "cp /etc/apache2/ports.conf /etc/apache2/ports.conf.bak"
    file_properties "/etc/apache2/ports.conf.bak" "root" "root" "-rw-r--r--" "644" "false"
fi

# apache2 : Add port configs for ports 8081 and 8090 to file /etc/apache2/ports.conf
# apache2 : Config is reloaded in FabQR start script
if ! ( ( cat "/etc/apache2/ports.conf" | grep '^NameVirtualHost \*\:8081$' ) > "/dev/null" )
then
    output_text "[INFO] Adding line NameVirtualHost *:8081 to file /etc/apache2/ports.conf"
    command_success "echo >> /etc/apache2/ports.conf"
    command_success "echo '# FabQR port 8081' >> /etc/apache2/ports.conf"
    command_success "echo 'NameVirtualHost *:8081' >> /etc/apache2/ports.conf"
fi

if ! ( ( cat "/etc/apache2/ports.conf" | grep '^Listen 8081$' ) > "/dev/null" )
then
    output_text "[INFO] Adding line Listen 8081 to file /etc/apache2/ports.conf"
    command_success "echo >> /etc/apache2/ports.conf"
    command_success "echo '# FabQR port 8081' >> /etc/apache2/ports.conf"
    command_success "echo 'Listen 8081' >> /etc/apache2/ports.conf"
fi

if ! ( ( cat "/etc/apache2/ports.conf" | grep '^NameVirtualHost \*\:8090$' ) > "/dev/null" )
then
    output_text "[INFO] Adding line NameVirtualHost *:8090 to file /etc/apache2/ports.conf"
    command_success "echo >> /etc/apache2/ports.conf"
    command_success "echo '# FabQR port 8090' >> /etc/apache2/ports.conf"
    command_success "echo 'NameVirtualHost *:8090' >> /etc/apache2/ports.conf"
fi

if ! ( ( cat "/etc/apache2/ports.conf" | grep '^Listen 8090$' ) > "/dev/null" )
then
    output_text "[INFO] Adding line Listen 8090 to file /etc/apache2/ports.conf"
    command_success "echo >> /etc/apache2/ports.conf"
    command_success "echo '# FabQR port 8090' >> /etc/apache2/ports.conf"
    command_success "echo 'Listen 8090' >> /etc/apache2/ports.conf"
fi

# apache2 : Reload config
if ! [ -e "/etc/apache2/sites-enabled/fabqr_apache_public" ]
then
    command_success "a2ensite fabqr_apache_public"
fi

if ! [ -e "/etc/apache2/sites-enabled/fabqr_apache_private" ]
then
    command_success "a2ensite fabqr_apache_private"
fi

command_success "service apache2 reload"

# USB power settings: enable maximum power on usb
if [ -e "/boot/config.txt" ]
then
    # USB power settings: Create backup
    if ! [ -e "/boot/config.txt.bak" ]
    then
        output_text "[INFO] Backup original boot settings config file /boot/config.txt"
        command_success "cp /boot/config.txt /boot/config.txt.bak"
        file_properties "/boot/config.txt.bak" "root" "root" "-rwxr-xr-x" "755" "false"
    fi

    # USB power settings : Place hash in front of all max_usb_current= lines
    command_success "sed -r -i 's/^(max_usb_current=.*)$/# \1/g' /boot/config.txt"

    # USB power settings : Remove hash in front of BLANK_TIME=1
    command_success "sed -r -i 's/^# (max_usb_current=1)$/\1/g' /boot/config.txt"

    # USB power settings : If file does not contain line max_usb_current=1, then add it
    if ! ( ( cat "/boot/config.txt" | grep ^max_usb_current=1$ ) > "/dev/null" )
    then
        output_text "[INFO] Adding line max_usb_current=1 to file /boot/config.txt"
        output_text "[INFO] You need to reboot to activate changes!"
        reboot=true
        command_success "echo >> /boot/config.txt"
        command_success "echo '# FabQR USB power setting' >> /boot/config.txt"
        command_success "echo 'max_usb_current=1' >> /boot/config.txt"
    fi
fi

# fail2ban : FabQR jail config, get file
copy_fabqr_file "fail2ban_configs/jail.local" "/etc/fail2ban/jail.local"
file_properties "/etc/fail2ban/jail.local" "root" "root" "-rw-r--r--" "644" "false"

# fail2ban : FabQR http filter config, get file
copy_fabqr_file "fail2ban_configs/fabqr-http.conf" "/etc/fail2ban/filter.d/fabqr-http.conf"
file_properties "/etc/fail2ban/filter.d/fabqr-http.conf" "root" "root" "-rw-r--r--" "644" "false"

# fail2ban : FabQR auth filter config, get file
copy_fabqr_file "fail2ban_configs/fabqr-auth.conf" "/etc/fail2ban/filter.d/fabqr-auth.conf"
file_properties "/etc/fail2ban/filter.d/fabqr-auth.conf" "root" "root" "-rw-r--r--" "644" "false"

# fail2ban : Reload config
command_success "service fail2ban reload"

output_text "[INFO] FabQR files and settings checked successfully"

# ##################################################################
# FABQR GRAPHICS
# ##################################################################

output_text "[INFO] Checking FabQR graphics"

# Display settings: disable standby of display
if [ -e "/etc/kbd/config" ]
then
    # Display settings: Create backup
    if ! [ -e "/etc/kbd/config.bak" ]
    then
        output_text "[INFO] Backup original display settings config file for kbd /etc/kbd/config"
        command_success "cp /etc/kbd/config /etc/kbd/config.bak"
        file_properties "/etc/kbd/config.bak" "root" "root" "-rw-r--r--" "644" "false"
    fi

    # Display settings : Place hash in front of all BLANK_TIME= and POWERDOWN_TIME= lines
    command_success "sed -r -i 's/^(BLANK_TIME=.*)$/# \1/g' /etc/kbd/config"
    command_success "sed -r -i 's/^(POWERDOWN_TIME=.*)$/# \1/g' /etc/kbd/config"

    # Display settings : Remove hash in front of BLANK_TIME=0 or POWERDOWN_TIME=0
    command_success "sed -r -i 's/^# (BLANK_TIME=0)$/\1/g' /etc/kbd/config"
    command_success "sed -r -i 's/^# (POWERDOWN_TIME=0)$/\1/g' /etc/kbd/config"

    # Display settings : If file does not contain line BLANK_TIME=0, then add it
    if ! ( ( cat "/etc/kbd/config" | grep ^BLANK_TIME=0$ ) > "/dev/null" )
    then
        output_text "[INFO] Adding line BLANK_TIME=0 to file /etc/kbd/config"
        output_text "[INFO] You need to reboot to activate changes!"
        reboot=true
        command_success "echo >> /etc/kbd/config"
        command_success "echo '# FabQR display setting' >> /etc/kbd/config"
        command_success "echo 'BLANK_TIME=0' >> /etc/kbd/config"
    fi

    # Display settings : If file does not contain line POWERDOWN_TIME=0, then add it
    if ! ( ( cat "/etc/kbd/config" | grep ^POWERDOWN_TIME=0$ ) > "/dev/null" )
    then
        output_text "[INFO] Adding line POWERDOWN_TIME=0 to file /etc/kbd/config"
        output_text "[INFO] You need to reboot to activate changes!"
        reboot=true
        command_success "echo >> /etc/kbd/config"
        command_success "echo '# FabQR display setting' >> /etc/kbd/config"
        command_success "echo 'POWERDOWN_TIME=0' >> /etc/kbd/config"
    fi
fi

# FabQR graphics: Directory
if ! [ -d "/home/fabqr/framebuffer_png_source" ]
then
    command_success "mkdir /home/fabqr/framebuffer_png_source"
fi

command_success "chown fabqr /home/fabqr/framebuffer_png_source -R"
command_success "chgrp fabqr /home/fabqr/framebuffer_png_source -R"
command_success "chmod 770 /home/fabqr/framebuffer_png_source -R"

# FabQR graphics: Files
copy_fabqr_file "framebuffer_png_source/fabqr_framebuffer_png.cpp" "/home/fabqr/framebuffer_png_source/fabqr_framebuffer_png.cpp"
copy_fabqr_file "framebuffer_png_source/lodepng.cpp" "/home/fabqr/framebuffer_png_source/lodepng.cpp"
copy_fabqr_file "framebuffer_png_source/lodepng.h" "/home/fabqr/framebuffer_png_source/lodepng.h"

# Compile program
output_text "[INFO] Compile FabQR framebuffer PNG graphics"
command_success "g++ /home/fabqr/framebuffer_png_source/lodepng.cpp /home/fabqr/framebuffer_png_source/fabqr_framebuffer_png.cpp -o /home/fabqr/fabqr_framebuffer_png -ansi -pedantic -Wall -Wextra -O3"

output_text "[INFO] FabQR graphics checked successfully"

# ##################################################################
# CHECK REBOOT VARIABLE
# ##################################################################

if ( $reboot )
then
    if user_confirm "[INFO] Optional: Reboot to activate all changes" "false"
    then
        reboot
        exit 0
    fi
fi

# ##################################################################
# EXIT AND START FABQR
# ##################################################################

# Exit correctly without errors
output_text "[INFO] QUIT FABQR INSTALLER SUCCESSFULLY"
command_success "service fabqr start"
exit 0