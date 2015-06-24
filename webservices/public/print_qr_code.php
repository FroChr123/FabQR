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
if (empty($_GET) || empty($_GET["projectId"]) || empty($_GET["type"]))
{
    quit_errorcode();
}

// Check type valid
$type = $_GET["type"];

if ($type != "public" && $type != "private")
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

if ($type == "private")
{
    $qrCodeFilePath = DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" $projectId . ".png";
}

if (!file_exists($qrCodeFilePath))
{
    quit_errorcode();
}

// Can not use default header template here, body onload print

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

// Header information
$pageTitle = escape_and_encode(TITLE_PREFIX . FABLAB_NAME . TITLE_SEPERATOR . TITLE_PRINT_QR_CODE, "xhtml", "");
$pageIcon = escape_and_encode(PUBLIC_URL . ICON_NAME, "xhtml", "");
$pageStyle = escape_and_encode(PUBLIC_URL . STYLE_NAME, "xhtml", "");

$printTemplate = str_replace("&&&TEMPLATE_PAGE_TITLE&&&", $pageTitle, $printTemplate);
$printTemplate = str_replace("&&&TEMPLATE_PAGE_ICON&&&", $pageIcon, $printTemplate);
$printTemplate = str_replace("&&&TEMPLATE_PAGE_STYLE&&&", $pageStyle, $printTemplate);

// Content information
$imageLinkQrCode = escape_and_encode(PUBLIC_URL . $projectId . "/" . FILENAME_IMAGE_QR_CODE, "xhtml", "");

if ($type == "private")
{
    $imageLinkQrCode = escape_and_encode(DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png", "xhtml", "");
}

$printTemplate = str_replace("&&&TEMPLATE_IMAGE_QR_CODE_LINK&&&", $imageLinkQrCode, $printTemplate);

// Output page
echo $printTemplate;

?>