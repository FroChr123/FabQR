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

// Get a free new identifier
$projectId = add_new_project(true, "");

if (empty($projectId))
{
    quit_errorcode();
}

// Process upload
$imageLinkQrCode = upload_temporary_private_file($_FILES, $projectId);

if (empty($imageLinkQrCode))
{
    quit_removeproject_errorcode($projectId, true);
}

// Output valid result
echo $imageLinkQrCode;

?>