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

// Process header template
$pageTitle = escape_and_encode(FABQR_PREFIX . FABLAB_NAME, "xhtml", "");
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
$templateContentPath = "../includes/xhtml_templates/template_main.xhtml";

if (!file_exists($templateContentPath))
{
    quit_errorcode();
}

$contentTemplate = file_get_contents($templateContentPath);

if (empty($contentTemplate))
{
    quit_errorcode();
}

// Process main content template
$allProjectTexts = "";
$pageMainHeading = escape_and_encode(FABQR_PREFIX . FABLAB_NAME, "xhtml", "");
$linkMain = escape_and_encode(PUBLIC_URL, "xhtml", "");

// Compute project indexes and pages
// Page count and project count start at 0
$page = 0;
$projectCount = count_projects_in_xml(false) - 1;

if ($projectCount < 0)
{
    $projectCount = 0;
}

$maxPage = (int)($projectCount / PROJECTS_PER_SITE);

// Check GET for optional page parameter, check valid range, fallback to page 0 otherwise
// GET parameter is increased by 1 for better display for user, decrease it
if (!empty($_GET["page"]))
{
    $page = intval($_GET["page"]);
    $page = ($page - 1);

    if ($page < 0 || $page > $maxPage)
    {
        $page = 0;
    }
}

// Get projects for specified range
$projectIds = get_latest_projects_in_xml(PROJECTS_PER_SITE, PROJECTS_PER_SITE * $page, false);

// Iterate project ids and get their contents
foreach ($projectIds as $projectId)
{
    $projectText = "";
    $projectFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_PROJECT_XHTML;

    if (file_exists($projectFilePath))
    {
        $projectText = file_get_contents($projectFilePath);
    }

    if (!empty($projectText))
    {
        $allProjectTexts = $allProjectTexts . $projectText . "\n";
    }
}

// Compute navigation values
$goPageDisabledAttribute = (($page <= 0 && $page == $maxPage) ? " disabled=\"disabled\"" : "");

// Note: Page indexes shifted here
$previousPageLink = (($page <= 0) ? "#" : PUBLIC_URL . PHP_SCRIPT_INDEX . "?page=" . $page);
$previousPageDisabledClass = (($page <= 0) ? " main-previous-link-disabled" : "");
$previousPageDisabledJs = (($page <= 0) ? " onclick=\"return false;\"" : "");

// Note: Page indexes shifted here
$nextPageLink = (($page >= $maxPage) ? "#" : PUBLIC_URL . PHP_SCRIPT_INDEX . "?page=" . ($page + 2));
$nextPageDisabledClass = (($page >= $maxPage) ? " main-next-link-disabled" : "");
$nextPageDisabledJs = (($page >= $maxPage) ? " onclick=\"return false;\"" : "");

// Prepare variables
$pageText = escape_and_encode(($page + 1), "xhtml", "");
$maxPageText = escape_and_encode(($maxPage + 1), "xhtml", "");
$uploadFileLink = escape_and_encode(PUBLIC_URL . PHP_SCRIPT_UPLOAD_FILE, "xhtml", "");

$goPageDisabledAttribute = escape_and_encode($goPageDisabledAttribute, "xhtml", "");

$previousPageLink = escape_and_encode($previousPageLink, "xhtml", "");
$previousPageDisabledClass = escape_and_encode($previousPageDisabledClass, "xhtml", "");
$previousPageDisabledJs = escape_and_encode($previousPageDisabledJs, "xhtml", "");

$nextPageLink = escape_and_encode($nextPageLink, "xhtml", "");
$nextPageDisabledClass = escape_and_encode($nextPageDisabledClass, "xhtml", "");
$nextPageDisabledJs = escape_and_encode($nextPageDisabledJs, "xhtml", "");

// Main content information
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADING&&&", $pageMainHeading, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);

$contentTemplate = str_replace("&&&TEMPLATE_PAGE_PROJECT_TEXTS&&&", $allProjectTexts, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_INDEX&&&", $pageText, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAX_INDEX&&&", $maxPageText, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_UPLOAD_FILE_LINK&&&", $uploadFileLink, $contentTemplate);

$contentTemplate = str_replace("&&&TEMPLATE_PAGE_GO_DISABLED&&&", $goPageDisabledAttribute, $contentTemplate);

$contentTemplate = str_replace("&&&TEMPLATE_PAGE_PREVIOUS_LINK&&&", $previousPageLink, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_PREVIOUS_DISABLED_CLASS&&&", $previousPageDisabledClass, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_PREVIOUS_DISABLED_JS&&&", $previousPageDisabledJs, $contentTemplate);

$contentTemplate = str_replace("&&&TEMPLATE_PAGE_NEXT_LINK&&&", $nextPageLink, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_NEXT_DISABLED_CLASS&&&", $nextPageDisabledClass, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_NEXT_DISABLED_JS&&&", $nextPageDisabledJs, $contentTemplate);

// Output page
echo $headerTemplate . $contentTemplate . $footerTemplate;

?>