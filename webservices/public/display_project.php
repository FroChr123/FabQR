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

// GET parameter is increased by 1 for better display for user, decrease it
if (empty($_GET) || empty($_GET["projectId"]))
{
    quit_errorcode();
}

$projectId = $_GET["projectId"];

// Check projectId syntax valid
if (!is_project_id_syntax_valid($projectId))
{
    quit_errorcode();
}

// Check if project file exists
$projectFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_PROJECT_XHTML;

if (!file_exists(DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_PROJECT_XHTML))
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

// Process header template
$pageTitle = escape_and_encode(SYSTEM_NAME, "xhtml", "");
$pageIcon = escape_and_encode(PUBLIC_URL . ICON_NAME, "xhtml", "");
$pageStyle = escape_and_encode(PUBLIC_URL . STYLE_NAME, "xhtml", "");

// Header information
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_TITLE&&&", $pageTitle, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_ICON&&&", $pageIcon, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_STYLE&&&", $pageStyle, $headerTemplate);

// Load footer template
$templateFooterPath = "../includes/xhtml_templates/template_footer.xhtml";

if (!file_exists($templateFooterPath))
{
    quit_errorcode();
}

$footerTemplate = file_get_contents($templateFooterPath);

if (empty($footerTemplate))
{
    quit_errorcode();
}

// Load main content template
$templateContentPath = "../includes/xhtml_templates/template_display_project.xhtml";

if (!file_exists($templateContentPath))
{
    quit_errorcode();
}

$contentTemplate = file_get_contents($templateContentPath);

if (empty($contentTemplate))
{
    quit_errorcode();
}

// Get contents of project
$projectText = file_get_contents($projectFilePath);

if (empty($projectText))
{
    quit_errorcode();
}

// Main content information
$pageMainHeading = escape_and_encode(SYSTEM_NAME, "xhtml", "");
$linkMain = escape_and_encode(PUBLIC_URL, "xhtml", "");

$contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADING&&&", $pageMainHeading, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);

$contentTemplate = str_replace("&&&TEMPLATE_PAGE_PROJECT_TEXT&&&", $projectText, $contentTemplate);

// Output page
echo $headerTemplate . $contentTemplate . $footerTemplate;

?>