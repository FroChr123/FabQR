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

// Function to generate QR codes to a specified file path
function generate_qr_code($text, $filepath)
{
    // Generate QR code with modified background color
    // QR code configs
    $pixelConfig = 8;
    $frameConfig = 4;
    $eclevelConfig = QR_ECLEVEL_H;

    // Color configs
    $bg_r = 229;
    $bg_g = 229;
    $bg_b = 229;
    $fg_r = 0;
    $fg_g = 0;
    $fg_b = 0;

    // Start processing
    $qrFrame = QRcode::text($text, false, $eclevelConfig, $pixelConfig, $frameConfig);

    // Render image with GD
    $imageHeight = count($qrFrame);
    $imageWidth = strlen($qrFrame[0]);

    // Prepare image
    $image = imagecreate($imageWidth, $imageHeight);
    $color_bg = imagecolorallocate($image, $bg_r, $bg_g, $bg_b);
    $color_fg = imagecolorallocate($image, $fg_r, $fg_g, $fg_b);

    // Image with background color
    imagefill($image, 0, 0, $color_bg);

    // Iterate each pixel and color it with foreground color if needed by QR code
    for ($y = 0; $y < $height; $y++)
    {
        for ($x = 0; $x < $width; $x++)
        {
            if ($qrFrame[$y][$x] == '1')
            {
                imagesetpixel($image, $x, $y, $color_fg);
            }
        }
    }

    // Draw 1 pixel border on QR code, for printing and cutting out the image
    imagerectangle($image, 0, 0, $imageWidth - 1, $imageHeight - 1, $color_fg);

    // Save target image to filesystem and deallocate
    imagepng($image, $filepath);
    imagecolordeallocate($color_bg);
    imagecolordeallocate($color_fg);
    imagedestroy($image);

    // Check if file exists now
    if (!file_exists($filepath))
    {
        return false;
    }

    return true;
}

// Function to compute correct indentation hierachies
function indentation($count)
{
    $result = "";

    for ($i = 0; $i < $count; $i++)
    {
        $result = $result . GENERAL_INDENTATION;
    }

    return $result;
}

// Function to generate correct encoded / escaped values
function escape_and_encode($text, $mode, $linebreakReplacement)
{
    $result = "";

    switch ($mode)
    {
        case 'xml':
            $result = htmlspecialchars(strval($text), ENT_QUOTES | ENT_XML1, 'UTF-8');
            break;
        case 'xhtml':
            $result = htmlspecialchars(strval($text), ENT_QUOTES | ENT_XHTML, 'UTF-8');
            break;
        default:
            break;
    }

    $result = str_replace("\r\n", strval($linebreakReplacement), $text);
    $result = str_replace("\n\r", strval($linebreakReplacement), $text);
    $result = str_replace("\r", strval($linebreakReplacement), $text);
    $result = str_replace("\n", strval($linebreakReplacement), $text);

    return $result;
}

// Function to check if given projectId is syntactically correct
function is_project_id_syntax_valid($projectId)
{
    // Valid project ids are 7 characters long
    if (strlen(strval($projectId)) == 7)
    {
        for ($i = 0; $i < 7; $i++)
        {
            $asciiVal = ord($projectId[$i]);

            // 48 = 0, 122 = z, 57 = 9, 97 = a
            if ($asciiVal < 48 || $asciiVal > 122 || ($asciiVal > 57 && $asciiVal < 97))
            {
                return false;
            }
        }

        return true;
    }

    return false;
}

?>