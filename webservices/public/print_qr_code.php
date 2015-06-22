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
if (empty($_GET) || empty($_GET["projectId"]))
{
    quit_errorcode();
}

// Check projectId syntax valid
$projectId = $_GET["projectId"];

if (!is_project_id_syntax_valid($projectId))
{
    quit_errorcode();
}

// Check if file exists
$qrCodeFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_IMAGE_QR_CODE;

if (!file_exists($qrCodeFilePath))
{
    quit_errorcode();
}

// Load print template
$templatePath = "../includes/xhtml_templates/template_print.xhtml";

if (!file_exists($templatePath))
{
    quit_errorcode();
}

$printTemplate = file_get_contents($templatePath);

if (empty($printTemplate))
{
    quit_errorcode();
}

$pageTitle = FABLAB_NAME . TITLE_SEPERATOR . TITLE_PRINT_QR_CODE;
$pageIcon = PUBLIC_URL . ICON_NAME;
$imageLinkQrCode = PUBLIC_URL . $projectId . "/" . FILENAME_IMAGE_QR_CODE;

// Header information
$printTemplate = str_replace("_--TEMPLATE_PAGE_TITLE--_", $pageTitle, $projectTemplate);
$printTemplate = str_replace("_--TEMPLATE_PAGE_ICON--_", $pageIcon, $projectTemplate);

// Body information
$printTemplate = str_replace("_--TEMPLATE_IMAGE_QR_CODE_LINK--_", $imageLinkQrCode, $projectTemplate);

// Output page
echo $printTemplate;

?>