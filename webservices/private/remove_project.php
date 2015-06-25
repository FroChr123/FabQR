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

$footerTemplate = file_get_contents($templateFooterPath);

if (empty($footerTemplate))
{
    quit_errorcode();
}

// Header information
$pageTitle = escape_and_encode(FABQR_PREFIX . FABLAB_NAME . TITLE_SEPERATOR . TITLE_REMOVE_PROJECT, "xhtml", "");
$pageIcon = escape_and_encode(PUBLIC_URL . ICON_NAME, "xhtml", "");
$pageStyle = escape_and_encode(PUBLIC_URL . STYLE_NAME, "xhtml", "");

$headerTemplate = str_replace("&&&TEMPLATE_PAGE_TITLE&&&", $pageTitle, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_ICON&&&", $pageIcon, $headerTemplate);
$headerTemplate = str_replace("&&&TEMPLATE_PAGE_STYLE&&&", $pageStyle, $headerTemplate);

// Prepare Content information
$contentTemplate = "";
$pageMainHeading = escape_and_encode(FABQR_PREFIX . FABLAB_NAME, "xhtml", "");
$pageSubHeading = escape_and_encode(TITLE_REMOVE_PROJECT, "xhtml", "");
$linkMain = escape_and_encode(PUBLIC_URL, "xhtml", "");

// Load content template
$templateContentPath = "../includes/xhtml_templates/template_remove_project.xhtml";

if (!file_exists($templateContentPath))
{
    quit_errorcode();
}

$contentTemplate = file_get_contents($templateContentPath);

if (empty($contentTemplate))
{
    quit_errorcode();
}

// Content information
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_MAIN_HEADING&&&", $pageMainHeading, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_PAGE_SUB_HEADING&&&", $pageSubHeading, $contentTemplate);
$contentTemplate = str_replace("&&&TEMPLATE_LINK_MAIN&&&", $linkMain, $contentTemplate);

$linkRemoveProject = escape_and_encode(PRIVATE_URL . PHP_SCRIPT_REMOVE_PROJECT, "xhtml", "");
$contentTemplate = str_replace("&&&TEMPLATE_LINK_REMOVE_PROJECT&&&", $linkRemoveProject, $contentTemplate);

$removeProjectResult = "";

// Process POST values
if (!empty($_POST))
{
    $removeProjectResult = "Project was successfully removed.";

    if (!empty($_POST["admin-password"]) && !empty($_POST["project-id"]))
    {
        $adminPassword = $_POST["admin-password"];

        if ($adminPassword === ADMIN_PASSWORD)
        {
            $projectId = $_POST["project-id"];
            $isPrivate = isset($_POST["private"]);

            if (is_project_id_syntax_valid($projectId))
            {
                $result = remove_project($projectId, $isPrivate);

                if (empty($result))
                {
                    $removeProjectResult = "Error in removing project!";
                }
                else
                {
                    $removeProjectResult = "Project was successfully removed!";
                }
            }
            else
            {
                $removeProjectResult = "Project id syntactically incorrect!";
            }
        }
        else
        {
            $removeProjectResult = "Admin password incorrect!";
        }
    }
    else
    {
        $removeProjectResult = "POST values incorrect!";
    }
}

$contentTemplate = str_replace("&&&TEMPLATE_REMOVE_PROJECT_RESULT&&&", $removeProjectResult, $contentTemplate);

// Output page
echo $headerTemplate . $contentTemplate . $footerTemplate;

?>