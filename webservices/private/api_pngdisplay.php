<?php

//    This file is part of FabQR. (https://github.com/FroChr123/FabQR)
//
//    FabQR is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Lesser General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    FabQR is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU Lesser General Public License
//    along with FabQR.  If not, see <http://www.gnu.org/licenses/>.

// Includes
require_once("../includes/config.php");
require_once("../includes/functions.php");

// Get FILES data, PNG bytes
if (empty($_FILES) || empty($_FILES["data"]) || empty($_FILES["data"]["tmp_name"]) || empty($_FILES["data"]["size"]))
{
    quit_errorcode();
}

// Open file handle, create file if does not exist, do not truncate file (as "w" would do)
$file = fopen(FILE_PNG_PATH, "c");

// Check if file opening is successful
if ($file === false)
{
    quit_errorcode();
}

// Lock file, check if successful
if (!flock($file, LOCK_EX))
{
    quit_errorcode();
}

// Copy file from temp directory to real file
if (!copy($_FILES["data"]["tmp_name"], FILE_PNG_PATH))
{
    quit_errorcode();
}

// Unlock file
if (!flock($file, LOCK_UN))
{
    quit_errorcode();
}

// Close file handle
if (!fclose($file))
{
    quit_errorcode();
}

?>