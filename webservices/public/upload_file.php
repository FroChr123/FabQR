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

// Load header template
$templateHeaderPath = "../includes/xhtml_templates/template_header.xhtml";

if (!file_exists($templateHeaderPath))
{
    quit_errorcode();
}

$headerTemplate = file_get_contents($templateHeaderPath);

if (empty($headerTemplate))
{
    quit_errorcode();
}

// Load footer template
$templateFooterPath = "../includes/xhtml_templates/template_footer.xhtml";

if (!file_exists($templateFooterPath))
{
    quit_errorcode();
}

$footerTemplate = file_get_contents($footerTemplate);

if (empty($footerTemplate))
{
    quit_errorcode();
}

// Header information
$pageTitle = escape_and_encode(FABQR_PREFIX . FABLAB_NAME . TITLE_SEPERATOR . TITLE_UPLOAD_FILE, "xhtml", "");
$pageIcon = escape_and_encode(PUBLIC_URL . ICON_NAME, "xhtml", "");
$pageStyle = escape_and_encode(PUBLIC_URL . STYLE_NAME, "xhtml", "");

$headerTemplate = str_replace("&&&TEMPLATE_PAGE_TITLE&&&", $pageTitle, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_ICON&&&", $pageIcon, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_STYLE&&&", $pageStyle, $headerTemplate);

// Prepare Content information
$contentTemplate = "";
$pageMainHeading = escape_and_encode(FABQR_PREFIX . FABLAB_NAME, "xhtml", "");
$pageSubHeading = escape_and_encode(TITLE_UPLOAD_FILE, "xhtml", "");
$linkMain = escape_and_encode(PUBLIC_URL, "xhtml", "");

// Output template with form upload
if (empty($_FILES))
{
    // Load content template
    $templateContentPath = "../includes/xhtml_templates/template_upload_file.xhtml";

    if (!file_exists($templateContentPath))
    {
        quit_errorcode();
    }

    $contentTemplate = file_get_contents($templateContentPath);

    if (empty($contentTemplate))
    {
        quit_errorcode();
    }

    $extensions = escape_and_encode(FILE_UPLOAD_EXTENSIONS, "xhtml", "");
    $fileSize = escape_and_encode(FILE_UPLOAD_MAXIMUM_SIZE_BYTES, "xhtml", "");
    $linkUpload = escape_and_encode(PUBLIC_URL . PHP_SCRIPT_UPLOAD_FILE, "xhtml", "");

    // Content information
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADER&&&", $pageMainHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_SUB_HEADER&&&", $pageSubHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);

    $contentTemplate = str_replace("&&&TEMPLATE_LINK_UPLOAD&&&", $linkUpload, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_UPLOAD_ALLOWED_EXTENSIONS&&&", $extensions, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_UPLOAD_MAXIMUM_SIZE_BYTES&&&", $fileSize, $contentTemplate);
}
// Output template with finished upload QR code
else
{
    // Load content template
    $templateContentPath = "../includes/xhtml_templates/template_upload_file_result.xhtml";

    if (!file_exists($templateContentPath))
    {
        quit_errorcode();
    }

    $contentTemplate = file_get_contents($templateContentPath);

    if (empty($contentTemplate))
    {
        quit_errorcode();
    }

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

    // Result is valid
    $imageLinkQrCode = escape_and_encode($imageLinkQrCode, "xhtml", "");

    // Link to print QR code
    $printQrCodeLink = escape_and_encode(PUBLIC_URL . PHP_SCRIPT_PRINT_QR_CODE . "?projectId=" . $projectId . "&type=private", "xhtml", "");

    // Link to email QR code
    $emailQrCodeLink = escape_and_encode(PUBLIC_URL . PHP_SCRIPT_EMAIL_QR_CODE . "?projectId=" . $projectId . "&type=private", "xhtml", "");

    // Content information
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADING&&&", $pageMainHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_SUB_HEADING&&&", $pageSubHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);

    $contentTemplate = str_replace("&&&TEMPLATE_IMAGE_QR_CODE_LINK&&&", $imageLinkQrCode, $contentTemplate);
    $projectTemplate = str_replace("&&&TEMPLATE_PRINTQRCODELINK&&&", $printQrCodeLink, $projectTemplate);
    $projectTemplate = str_replace("&&&TEMPLATE_EMAILQRCODELINK&&&", $emailQrCodeLink, $projectTemplate);
}

echo $headerTemplate . $contentTemplate . $footerTemplate;

?>