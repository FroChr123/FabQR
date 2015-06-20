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
require_once("phpqrcode/qrlib.php");

// Function which sets error header and quits script execution
function quit_errorcode()
{
    // Set error header
    http_response_code(500);

    // Stop script execution
    exit();
}

// Function to quit and remove project
function quit_removeproject_errorcode($projectId, $isPrivate)
{
    // Try to remove project
    remove_project($projectId, $isPrivate);

    // Quit with error code
    quit_errorcode();
}

// Function which tries to find a new unused project id and adds a new empty project with this id
function add_new_project($isPrivate)
{
    // Set correct target path
    $path = DIR_PUBLIC_PATH;

    if (!empty($isPrivate))
    {
        $path = DIR_PRIVATE_PATH;
    }

    // Try multiple times to find a new project id
    for ($i = 0; $i < 20; $i++)
    {
        // Variable to store the result
        $newProjectId = "";

        // Length of a project id is 7 alphanumeric characters
        for ($j = 0; $j < 7; $j++)
        {
            // Random alphabetic character (26) or random number (10) => 36 possible entries
            $rand = rand(0, 35);

            // Numeric result
            if ($rand <= 9)
            {
                $newProjectId = $newProjectId . strval($rand);
            }
            // Alphabetic result
            else
            {
                // Shift range from 10 to 35 to range from 97 to 122 for ASCII characters a to z
                $newProjectId = $newProjectId . chr($rand + 87);
            }
        }

        // If directory does not exist, create it and return this project id
        if (!is_dir($path . $newProjectId))
        {
            // Check if creating new directory is successful
            if (mkdir($path . $newProjectId, 0770))
            {
                return $newProjectId;
            }
        }
    }

    return "";
}

// Function which tries to remove a project
function remove_project($projectId, $isPrivate)
{
    // Set correct target path
    $path = DIR_PUBLIC_PATH;

    if (!empty($isPrivate))
    {
        $path = DIR_PRIVATE_PATH;
    }

    // If directory exists, remove all files in it
    if (is_dir($path . $projectId))
    {
        // Directory has to be empty before it can be deleted, remove seperate files
        $files = scandir($path . $projectId);

        // Try to delete all files
        if (!empty($files))
        {
            foreach ($files as $file)
            {
                // Scandir also returns . and .. as relative paths to directories, ignore them in iteration
                if ($file == "." || $file == "..")
                {
                    continue;
                }

                // For all other files, try to delete them
                unlink($path . $projectId . "/" . $file);
            }
        }

        // Check if removing directory is successful
        if (rmdir($path . $projectId))
        {
            return true;
        }
    }

    return false;
}

?>