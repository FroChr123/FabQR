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

// Validate input: Get
if (empty($_GET) || empty($_GET["marker"]) || empty($_GET["projectId"]))
{
    quit_errorcode();
}

// Check marker
$marker = $_GET["marker"];

if ($marker != URL_MARKER_DOWNLOAD && $marker != URL_MARKER_TEMPORARY)
{
    quit_errorcode();
}

// Check project id
$projectId = $_GET["projectId"];

if (!is_project_id_syntax_valid($projectId))
{
    quit_errorcode();
}

// Find requested file
$dirPath = "";
$fileUrl = "";

// For download: List all files in public directory, remove all default files, only file remaining is desired file
if ($marker == URL_MARKER_DOWNLOAD)
{
    $dirPath = DIR_PUBLIC_PATH . $projectId;

    if (!is_dir($dirPath))
    {
        quit_errorcode();
    }

    // Get directory files
    $files = scandir($dirPath);

    if (empty($files))
    {
        quit_errorcode();
    }

    // Remove all default files, remaining file is project data file
    $filesRemove = array(DIR_LOCAL, DIR_PARENT, FILENAME_IMAGE_REAL, FILENAME_IMAGE_SCHEME, FILENAME_IMAGE_QR_CODE, FILENAME_PROJECT_XHTML);
    $filesProcessed = array_diff($files, $filesRemove);

    if (count($filesProcessed) != 1)
    {
        quit_errorcode();
    }

    // Get the value of the only entry in array, NOT located at key 0, use reset to get value
    $foundFileName = reset($filesProcessed);

    // Get final URL
    $fileUrl = PUBLIC_URL . $projectId . "/" . $foundFileName;
}
// For temporary: Only one file in private section
else if ($marker == URL_MARKER_TEMPORARY)
{
    $dirPath = DIR_PRIVATE_PATH . $projectId;

    if (!is_dir($dirPath))
    {
        quit_errorcode();
    }

    // Get directory files
    $files = scandir($dirPath);

    if (empty($files))
    {
        quit_errorcode();
    }

    // Remove all default files, remaining file is project data file
    $filesRemove = array(DIR_LOCAL, DIR_PARENT);
    $filesProcessed = array_diff($files, $filesRemove);

    if (count($filesProcessed) != 1)
    {
        quit_errorcode();
    }

    // Get the value of the only entry in array, NOT located at key 0, use reset to get value
    $foundFileName = reset($filesProcessed);

    // Get final URL
    $fileUrl = PRIVATE_URL . $projectId . "/" . $foundFileName;
}

// If no file url found, error
if (empty($fileUrl))
{
    quit_errorcode();
}

// Redirect to file url with permanent redirect
http_response_code(301);
header("Location: " . $fileUrl);

?>