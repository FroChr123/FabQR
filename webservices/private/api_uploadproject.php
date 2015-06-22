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

// Validate input: Files
if (empty($_FILES)
    || empty($_FILES["imageScheme"]) || empty($_FILES["imageScheme"]["tmp_name"]) || empty($_FILES["imageScheme"]["size"]) || empty($_FILES["imageScheme"]["name"])
    || empty($_FILES["inputFile"]) || empty($_FILES["inputFile"]["tmp_name"]) || empty($_FILES["inputFile"]["size"]) || empty($_FILES["inputFile"]["name"])
    || (isset($_FILES["imageReal"]) && empty($_FILES["imageReal"]) || empty($_FILES["imageReal"]["tmp_name"]) || empty($_FILES["imageReal"]["size"]) || empty($_FILES["imageReal"]["name"])))
{
    quit_errorcode();
}

// Validate input: Text data, need temporary variables for correct empty checks
$nameTmp = "";

if (isset($_POST["name"]))
{
    $nameTmp = trim($_POST["name"]);
}

$emailTmp = "";

if (isset($_POST["email"]))
{
    $emailTmp = trim($_POST["email"]);
}

$projectNameTmp = "";

if (isset($_POST["projectName"]))
{
    $projectNameTmp = trim($_POST["projectName"]);
}

$toolsTmp = "";

if (isset($_POST["tools"]))
{
    $toolsTmp = trim($_POST["tools"]);
}

$descriptionTmp = "";

if (isset($_POST["description"]))
{
    $descriptionTmp = trim($_POST["description"]);
}

$lasercutterNameTmp = "";

if (isset($_POST["lasercutterName"]))
{
    $lasercutterNameTmp = trim($_POST["lasercutterName"]);
}

$lasercutterMaterialTmp = "";

if (isset($_POST["lasercutterMaterial"]))
{
    $lasercutterMaterialTmp = trim($_POST["lasercutterMaterial"]);
}

$referencesTmp = "";

if (isset($_POST["references"]))
{
    $referencesTmp = trim($_POST["references"]);
}

if (empty($_POST)
    || !isset($_POST["name"]) || !isset($_POST["email"]) || !isset($_POST["licenseIndex"])
    || (intval(trim($_POST["licenseIndex"])) != LICENSE_CC0_INDEX && (empty($nameTmp) || empty($emailTmp)))
    || empty($projectNameTmp) || strlen(trim($_POST["projectName"])) < PROJECT_NAME_MINIMUM_LENGTH
    || empty($toolsTmp) || empty($descriptionTmp)
    || (isset($_POST["lasercutterName"]) && empty($lasercutterNameTmp))
    || (isset($_POST["lasercutterMaterial"]) && empty($lasercutterMaterialTmp))
    || (isset($_POST["references"]) && empty($referencesTmp)))
{
    quit_errorcode();
}

// Get a free new identifier
$projectId = add_new_project(false);

if (empty($projectId))
{
    quit_errorcode();
}

// Generate QR code for that project
$projectDownloadUrl = PUBLIC_URL . URL_MARKER_DOWNLOAD . "/" . $projectId;
$qrCodeFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_IMAGE_QR_CODE;

if (!generate_qr_code($projectDownloadUrl, $qrCodeFilePath))
{
    quit_removeproject_errorcode($projectId, false);
}

// Copy image scheme from temp directory to target file
$imageSchemeFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_IMAGE_SCHEME;

if (!copy($_FILES["imageScheme"]["tmp_name"], $imageSchemeFilePath))
{
    quit_removeproject_errorcode($projectId, false);
}

// Copy image real from temp directory to target file, if it was sent
if (isset($_FILES["imageReal"]))
{
    $imageRealFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_IMAGE_REAL;

    if (!copy($_FILES["imageReal"]["tmp_name"], $imageRealFilePath))
    {
        quit_removeproject_errorcode($projectId, false);
    }
}

// Determine file name based on project name, need to remove unallowed characters for URLs

// Remove trailing whitespace
$inputFileName = trim($_POST["projectName"]);

// Replace whitespace with underscore
$inputFileName = preg_replace("/\s/", "_", $inputFileName);

// Special replacements for some characters
$inputFileName = str_replace("Ä", "Ae", str_replace("Ö", "Oe", str_replace("Ü", "Ue", str_replace("ä", "ae", str_replace("ö", "oe", str_replace("ü", "ue", str_replace("ß", "ss", $inputFileName)))))));

// Remove all non alphabetic / numeric characters
$inputFileName = preg_replace("/[^0-9a-zA-Z_\-]/", "", $inputFileName);

// Append original input file type ending at end
$inputFileName = $inputFileName . str_replace("inputFile", "", $_FILES["inputFile"]["name"]);

// Copy input file from temp directory to target file
$inputFilePath = DIR_PUBLIC_PATH . $projectId . "/" . $inputFileName;

if (!copy($_FILES["inputFile"]["tmp_name"], $inputFilePath))
{
    quit_removeproject_errorcode($projectId, false);
}

// Process text data and prepare data items
// Name
$name = escape_and_encode(trim($_POST["name"]), "xhtml", "");

if (empty($name))
{
    $name = escape_and_encode(AUTHOR_NOT_SPECIFIED, "xhtml", "");
}

// Email
$emailHTML = escape_and_encode(trim($_POST["email"]), "xhtml", "");

if (empty($emailHTML))
{
    $emailHTML = escape_and_encode(EMAIL_NOT_SPECIFIED, "xhtml", "");
}
else
{
    $emailHTML = '<a href="mailto:' . $emailHTML . '" class="project-email-link">' . $emailHTML . '</a>';
}

// Project name
$projectName = escape_and_encode(trim($_POST["projectName"]), "xhtml", "");

// License
$licenseIndex = intval(trim($_POST["licenseIndex"]));
$licenseString = "";

switch ($licenseIndex)
{
    case LICENSE_CC0_INDEX:
        $licenseString = escape_and_encode(LICENSE_CC0_TEXT, "xhtml", "");
        break;
    case LICENSE_CCBY_INDEX:
        $licenseString = escape_and_encode(LICENSE_CCBY_TEXT, "xhtml", "");
        break;
    case LICENSE_CCBYSA_INDEX:
        $licenseString = escape_and_encode(LICENSE_CCBYSA_TEXT, "xhtml", "");
        break;
    case LICENSE_CCBYNC_INDEX:
        $licenseString = escape_and_encode(LICENSE_CCBYNC_TEXT, "xhtml", "");
        break;
    case LICENSE_CCBYND_INDEX:
        $licenseString = escape_and_encode(LICENSE_CCBYND_TEXT, "xhtml", "");
        break;
    case LICENSE_CCBYNCSA_INDEX:
        $licenseString = escape_and_encode(LICENSE_CCBYNCSA_TEXT, "xhtml", "");
        break;
    case LICENSE_CCBYNCND_INDEX:
        $licenseString = escape_and_encode(LICENSE_CCBYNCND_TEXT, "xhtml", "");
        break;
    default:
        break;
}

if (empty($licenseString))
{
    quit_removeproject_errorcode($projectId, false);
}

// Description
$description = escape_and_encode(replace_linebreaks(trim($_POST["description"]), "<br />"), "xhtml", "<br />");

// Tools
$tools = trim($_POST["tools"]);
$toolsArray = explode(TOOLS_SEPERATOR, $tools);
$toolsIndentation = TOOLS_MAIN_INDENTATION;
$toolsHTML = '<ul class="project-tools-list">\n';
$toolsIndentation1 = "";

foreach ($toolsArray as $tool)
{
    switch ($tool)
    {
        case TOOL_LASERCUTTER:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-lasercutter">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-lasercutter-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';

            // Lasercutter name
            $lasercutterName = "";

            if (isset($_POST["lasercutterName"]))
            {
                $lasercutterName = trim($_POST["lasercutterName"]);
            }

            // Lasercutter material
            $lasercutterMaterial = "";

            if (isset($_POST["lasercutterMaterial"]))
            {
                $lasercutterMaterial = trim($_POST["lasercutterMaterial"]);
            }

            // If either of them is not empty, create sub list
            if (!empty($lasercutterName) || !empty($lasercutterMaterial))
            {
                $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<ul class="project-tool-lasercutter-extended-list">\n';

                if (!empty($lasercutterName))
                {
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 3) . '<li class="project-tool-lasercutter-extended-model">\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 4) . '<span class="project-tool-lasercutter-extended-model-key">Model</span>\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 4) . '<span class="project-tool-lasercutter-extended-model-colon">' . escape_and_encode(COLON_TEXT, "xhtml", "") . '</span>\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 4) . '<span class="project-tool-lasercutter-extended-model-value">' . escape_and_encode($lasercutterName, "xhtml", "") . '</span>\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 3) . '</li>\n';
                }

                if (!empty($lasercutterMaterial))
                {
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 3) . '<li class="project-tool-lasercutter-extended-material">\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 4) . '<span class="project-tool-lasercutter-extended-material-key">Material</span>\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 4) . '<span class="project-tool-lasercutter-extended-material-colon">' . escape_and_encode(COLON_TEXT, "xhtml", "") . '</span>\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 4) . '<span class="project-tool-lasercutter-extended-material-value">' . escape_and_encode($lasercutterMaterial, "xhtml", "") . '</span>\n';
                    $toolsHTML = $toolsHTML . indentation($toolsIndentation + 3) . '</li>\n';
                }

                $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '</ul>\n';
            }

            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
        case TOOL_PCB_SOLDERING:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-pcb-soldering">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-pcb-soldering-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
        case TOOL_3D_PRINTER:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-3d-printer">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-3d-printer-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
        case TOOL_CNC_ROUTER:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-cnc-router">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-cnc-router-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
        case TOOL_ARDUINO:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-arduino">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-arduino-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
        case TOOL_RASPBERRY_PI:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-raspberry-pi">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-raspberry-pi-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
        default:
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '<li class="project-tool-unknown">\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 2) . '<span class="project-tool-unknown-text">' . escape_and_encode($tool, "xhtml", "") . '</span>\n';
            $toolsHTML = $toolsHTML . indentation($toolsIndentation + 1) . '</li>\n';
            break;
    }
}

$toolsHTML = $toolsHTML . indentation($toolsIndentation + 0) . '</ul>';

// References
$referencesHTML = "";

if (isset($_POST["references"]))
{
    $references = trim($_POST["references"]);
    $referencesArray = explode(REFERENCES_SEPERATOR, $references);
    $referencesIndentation = REFERENCES_MAIN_INDENTATION;
    $referencesCounter = 0;

    foreach ($referencesArray as $ref)
    {
        $referencesCounter++;

        if (empty($referencesHTML))
        {
            $referencesHTML = '<span class="project-references-key">' . escape_and_encode(REFERENCES_TEXT, "xhtml", "") . '</span>\n';
            $referencesHTML = $referencesHTML . indentation($referencesIndentation + 0) . '<span class="project-references-colon">' . escape_and_encode(COLON_TEXT, "xhtml", "") . '</span>\n';
            $referencesHTML = $referencesHTML . indentation($referencesIndentation + 0) . '<span class="project-references-value">\n';
        }
        else
        {
            $referencesHTML = $referencesHTML . indentation($referencesIndentation + 1) . '<span class="project-references-seperator">' . escape_and_encode(REFERENCES_HTML_SEPERATOR, "xhtml", "") . '</span>\n';
        }

        $referencesHTML = $referencesHTML . indentation($referencesIndentation + 1) . '<a href="' . escape_and_encode($ref) . '" class="project-references-link-' . $referencesCounter . '" target="_blank">' . escape_and_encode(REFERENCES_LINK_TEXT, "xhtml", "") . $referencesCounter . '</a>\n';
    }

    $referencesHTML = $referencesHTML . indentation($referencesIndentation + 0) . '</span>';
}

// Date
$dateString = escape_and_encode(date("j. F Y, H:i"), "xhtml", "");

// Link to project
$projectLink = PUBLIC_URL . $projectId;

// Link to project file
$projectFileLink = PUBLIC_URL . $projectId . "/" . $inputFileName;

// Links to images
$imageLinkScheme = PUBLIC_URL . $projectId . "/" . FILENAME_IMAGE_SCHEME;
$imageLinkQrCode = PUBLIC_URL . $projectId . "/" . FILENAME_IMAGE_QR_CODE;

// Link for real image is special, fallback to link scheme if it is not set to avoid errors in apache logs
// is not visible anyways in that case
$imageLinkReal = $imageLinkScheme;
$imageRealVisible = "display: none;";

if (isset($_FILES["imageReal"]))
{
    $imageLinkReal = PUBLIC_URL . $projectId . "/" . FILENAME_IMAGE_REAL;
    $imageRealVisible = "display: table-cell;";
}

// Link to print QR code
$printQrCodeLink = PUBLIC_URL . PHP_SCRIPT_PRINT_QR_CODE . "?projectId=" . $projectId;

// Load project template
$templatePath = "../includes/xhtml_templates/template_project.xhtml";

if (!file_exists($templatePath))
{
    quit_removeproject_errorcode($projectId, false);
}

$projectTemplate = file_get_contents($templatePath);

if (empty($projectTemplate))
{
    quit_removeproject_errorcode($projectId, false);
}

// Replace items in template text
// No header information
// Body information
$projectTemplate = str_replace("_--TEMPLATE_NAME--_", $name, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_EMAIL_HTML--_", $emailHTML, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_PROJECTNAME--_", $projectName, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_LICENSE--_", $licenseString, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_DESCRIPTION--_", $description, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_TOOLS_HTML--_", $toolsHTML, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_REFERENCES_HTML--_", $referencesHTML, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_DATE--_", $dateString, $projectTemplate);

$projectTemplate = str_replace("_--TEMPLATE_IMAGE_SCHEME_LINK--_", $imageLinkScheme, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_IMAGE_REAL_LINK--_", $imageLinkReal, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_IMAGE_QR_CODE_LINK--_", $imageLinkQrCode, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_IMAGE_REAL_VISIBLE--_", $imageRealVisible, $projectTemplate);

$projectTemplate = str_replace("_--TEMPLATE_PRINTQRCODELINK--_", $printQrCodeLink, $projectTemplate);

$projectTemplate = str_replace("_--TEMPLATE_PROJECTLINK--_", $projectLink, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_PROJECTID--_", $projectId, $projectTemplate);
$projectTemplate = str_replace("_--TEMPLATE_PROJECTFILELINK--_", $projectFileLink, $projectTemplate);

// Write content to file
$projectFilePath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_PROJECT_XHTML;

if (file_put_contents($projectFilePath, $projectFilePath) === false)
{
    quit_removeproject_errorcode($projectId, false);
}

?>