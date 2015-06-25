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

// Check if temporary projects file exists
if (!file_exists(DIR_PRIVATE_PATH . FILENAME_PROJECTS_XML))
{
    exit();
}

// Deal with index file
$domDoc = new DOMDocument("1.0", "UTF-8");
$domDoc->preserveWhiteSpace = false;
$domDoc->formatOutput = true;

// Open file descriptor
$fileDescriptor = fopen(DIR_PRIVATE_PATH . FILENAME_PROJECTS_XML, "a+");

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

// Prepare array which contains the project ids to be deleted
$removeProjectIds = array();

// Compute timestamp
$timestamp = time() - DELETE_TEMPORARY_FILE_INTERVAL;

// Load index XML file
if (@$domDoc->load(DIR_PRIVATE_PATH . FILENAME_PROJECTS_XML) === true)
{
    // Get project nodes from XML file, which are old enough, use XPath
    $xpath = new DOMXPath($domDoc);
    $projectNodeList = $xpath->query("/index/project[@create-timestamp<$timestamp]");

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
                        $removeProjectIds[$i] = $projectNodeAttributesIdValue;
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

// Remove projects
if (!empty($removeProjectIds))
{
    foreach ($removeProjectIds as $projectId)
    {
        remove_project($projectId, true);
    }
}

?>