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

// Check GET data
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
$imageLinkQrCode = escape_and_encode(PUBLIC_URL . $projectId . "/" . FILENAME_IMAGE_QR_CODE, "xhtml", "");
$isPrivate = false;

if ($type == "private")
{
    $qrCodeFilePath = DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" $projectId . ".png";
    $imageLinkQrCode = escape_and_encode(PUBLIC_URL . DIR_NAME_PRIVATE_QR_CODES . "/" $projectId . ".png", "xhtml", "");
    $isPrivate = true;
}

if (!file_exists($qrCodeFilePath))
{
    quit_errorcode();
}

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
$pageTitle = escape_and_encode(FABQR_PREFIX . FABLAB_NAME . TITLE_SEPERATOR . TITLE_EMAIL_QR_CODE, "xhtml", "");
$pageIcon = escape_and_encode(PUBLIC_URL . ICON_NAME, "xhtml", "");
$pageStyle = escape_and_encode(PUBLIC_URL . STYLE_NAME, "xhtml", "");

$headerTemplate = str_replace("&&&TEMPLATE_PAGE_TITLE&&&", $pageTitle, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_ICON&&&", $pageIcon, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_STYLE&&&", $pageStyle, $headerTemplate);

// Prepare Content information
$contentTemplate = "";
$pageMainHeading = escape_and_encode(FABQR_PREFIX . FABLAB_NAME, "xhtml", "");
$pageSubHeading = escape_and_encode(TITLE_EMAIL_QR_CODE, "xhtml", "");
$linkMain = escape_and_encode(PUBLIC_URL, "xhtml", "");

// Output template with form email
if (empty($_POST))
{
    // Load content template
    $templateContentPath = "../includes/xhtml_templates/template_email_qr_code.xhtml";

    if (!file_exists($templateContentPath))
    {
        quit_errorcode();
    }

    $contentTemplate = file_get_contents($templateContentPath);

    if (empty($contentTemplate))
    {
        quit_errorcode();
    }

    $linkEmail = escape_and_encode(PUBLIC_URL . PHP_SCRIPT_EMAIL_QR_CODE, "xhtml", "");

    // Content information
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADER&&&", $pageMainHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_SUB_HEADER&&&", $pageSubHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_IMAGE_QR_CODE_LINK&&&", $imageLinkQrCode, $contentTemplate);

    $contentTemplate = str_replace("&&&TEMPLATE_LINK_EMAIL_QR_CODE&&&", $linkEmail, $contentTemplate);
}
// Output template with email result
else
{
    // Check POST data
    if (empty($_POST["email"]))
    {
        quit_errorcode();
    }

    // Simple regex email check
    $email = $_POST["email"];
    $regex_email = "^.+@.+\..+$";

    if (!preg_match($regex, $email))
    {
        quit_errorcode();
    }

    // Load content template
    $templateContentPath = "../includes/xhtml_templates/template_email_qr_code_result.xhtml";

    if (!file_exists($templateContentPath))
    {
        quit_errorcode();
    }

    $contentTemplate = file_get_contents($templateContentPath);

    if (empty($contentTemplate))
    {
        quit_errorcode();
    }

    // Prepare email
    $subject = EMAIL_QR_CODE_SUBJECT_PREFIX . FABLAB_NAME;

    // Send email
    function send_email($recipientMail, $subject, $htmlBody, $plainBody, $projectId = "", $isPrivate = false)
    if (!send_email($email, $subject, EMAIL_QR_CODE_BODY, EMAIL_QR_CODE_BODY, $projectId, $isPrivate))
    {
        quit_errorcode();
    }

    // Content information
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADING&&&", $pageMainHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_PAGE_SUB_HEADING&&&", $pageSubHeading, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);
    $contentTemplate = str_replace("&&&TEMPLATE_IMAGE_QR_CODE_LINK&&&", $imageLinkQrCode, $contentTemplate);
}

echo $headerTemplate . $contentTemplate . $footerTemplate;

?>