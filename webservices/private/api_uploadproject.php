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

    // Insert file uploads
    /*multipartEntityBuilder.addBinaryBody("imageScheme", imageSchemeBytes, ContentType.APPLICATION_OCTET_STREAM, "imageScheme.png");
    multipartEntityBuilder.addBinaryBody("plfFile", plfFileBytes, ContentType.APPLICATION_OCTET_STREAM, "plfFile.plf");

    // Image real is allowed to be null, if it is not, send it
    if (imageRealBytes != null)
    {
      multipartEntityBuilder.addBinaryBody("imageReal", plfFileBytes, ContentType.APPLICATION_OCTET_STREAM, "imageReal.png");
    }

    // Insert text data
    multipartEntityBuilder.addTextBody("name", name);
    multipartEntityBuilder.addTextBody("email", email);
    multipartEntityBuilder.addTextBody("licenseIndex", new Integer(licenseIndex).toString());
    multipartEntityBuilder.addTextBody("tools", tools);
    multipartEntityBuilder.addTextBody("description", description);
    multipartEntityBuilder.addTextBody("lasercutterName", lasercutterName);
    multipartEntityBuilder.addTextBody("materialString", materialString);
    multipartEntityBuilder.addTextBody("usedURLs", usedURLs);
*/

// Validate input: Files
if (empty($_FILES)
    || empty($_FILES["imageScheme"]) || empty($_FILES["imageScheme"]["tmp_name"]) || empty($_FILES["imageScheme"]["size"])
    || empty($_FILES["plfFile"]) || empty($_FILES["plfFile"]["tmp_name"]) || empty($_FILES["plfFile"]["size"])
    || (isset($_FILES["imageReal"]) && empty($_FILES["imageReal"]) || empty($_FILES["imageReal"]["tmp_name"]) || empty($_FILES["imageReal"]["size"])))
{
    quit_errorcode();
}

// Validate input: Text data
if (empty($_POST)
    || !isset($_POST["name"]) || !isset($_POST["email"]) || !isset($_POST["licenseIndex"])
    || empty($_POST["tools"]) || empty($_POST["description"])
    || (isset($_POST["lasercutterName"]) && empty($_POST["lasercutterName"]))
    || (isset($_POST["materialString"]) && empty($_POST["materialString"]))
    || (isset($_POST["usedURLs"]) && empty($_POST["usedURLs"])))
{
    quit_errorcode();
}

// Get a free new identifier
$projectId = add_new_project(false);

if (empty($projectId))
{
    quit_errorcode();
}

$projectDownloadUrl = PUBLIC_URL . URL_MARKER_DOWNLOAD . "/" . $projectId;

// Generate QR code for that project


// Copy file from temp directory to real file
if (!copy($_FILES["data"]["tmp_name"], DIR_PUBLIC_PATH . ))
{
    quit_errorcode();
}

?>