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
require_once("config.php");
require_once("functions.php");

// Set execution time limit to 6 hours
set_time_limit(6 * 3600);

// Increase memory limits
ini_set("memory_limit", "256M");

// Check settings
if (empty($argv[1]) || ($argv[1] !== "private" && $argv[1] !== "public"))
{
    die("ERROR - INVALID ARGUMENTS");
}

// Set correct path to XML file
$xmlpath = DIR_PUBLIC_PATH . FILENAME_PROJECTS_XML;

if ($argv[1] === "private")
{
    $xmlpath = DIR_PRIVATE_PATH . FILENAME_PROJECTS_XML;
}

// Check if file exists
if (!file_exists($xmlpath))
{
    die("ERROR - FILE $xmlpath DOES NOT EXIST");
}

// Deal with index file
$domDoc = new DOMDocument("1.0", "UTF-8");
$domDoc->preserveWhiteSpace = false;
$domDoc->formatOutput = true;

// Open file descriptor
$fileDescriptor = fopen($xmlpath, "a+");

// Check if file opening is successful
if ($fileDescriptor === false)
{
    exit();
}

// Lock file, check if successful
if (!flock($fileDescriptor, LOCK_EX))
{
    exit();
}

// Prepare array which contains the project ids to regenerated
$projectIds = array();

// Load index XML file
if (@$domDoc->load($xmlpath) === true)
{
    // Get project nodes from XML file
    $indexNode = $domDoc->firstChild;

    if (!empty($indexNode))
    {
        // List of child nodes
        if (!empty($indexNode->childNodes))
        {
            $projectNodeList = $indexNode->childNodes;

            // Iterate project node list
            for ($i = 0; $i < $projectNodeList->length; $i++)
            {
                // Access project id for each project node
                $projectNode = $projectNodeList->item($i);

                if (!empty($projectNode))
                {
                    $projectNodeAttributes = $projectNode->attributes;

                    if (!empty($projectNodeAttributes))
                    {
                        $projectNodeAttributesId = $projectNodeAttributes->getNamedItem("id");

                        if (!empty($projectNodeAttributesId))
                        {
                            $projectNodeAttributesIdValue = $projectNodeAttributesId->nodeValue;

                            if (!empty($projectNodeAttributesIdValue))
                            {
                                $projectIds[$i] = $projectNodeAttributesIdValue;
                            }
                        }
                    }
                }
            }
        }
    }
}

// Unlock file
if (!flock($fileDescriptor, LOCK_UN))
{
    exit();
}

// Close file handle
if (!fclose($fileDescriptor))
{
    exit();
}

// Regenerate QR codes
if (!empty($projectIds))
{
    foreach ($projectIds as $projectId)
    {
        $projectDownloadUrl = PUBLIC_URL . URL_MARKER_DOWNLOAD . "/" . $projectId;
        $qrCodeFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_IMAGE_QR_CODE;

        if ($argv[1] === "private")
        {
            $projectDownloadUrl = PRIVATE_URL . URL_MARKER_TEMPORARY . "/" . $projectId;
            $qrCodeFilePath = DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png";
        }

        if (file_exists($qrCodeFilePath))
        {
            unlink($qrCodeFilePath);

            if (!file_exists($qrCodeFilePath))
            {
                generate_qr_code($projectDownloadUrl, $qrCodeFilePath);
            }
        }
    }
}

?>