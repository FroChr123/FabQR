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

/* **********************************
 * CONFIGURED BY INSTALL SCRIPT
 * ********************************** */

// Public area URL
define("PUBLIC_URL", "http://192.168.178.38:8090/");

// Private area URL
define("PRIVATE_URL", "http://192.168.178.38:8081/");

// Absolute file path of the PNG file to write to
define("FILE_PNG_PATH", "/run/shm/pngdisplay.png");

// Name of the FabLab, used in titles
define("FABLAB_NAME", "FabLab");

/* **********************************
 * CONSTANTS
 * ********************************** */

// Absolute directory path of public area
define("DIR_PUBLIC_PATH", "/home/fabqr/fabqr_data/www/public/");

// Absolute directory path of private area
define("DIR_PRIVATE_PATH", "/home/fabqr/fabqr_data/www/private/");
 
// Filenames in project directories
define("FILENAME_IMAGE_REAL", "image_real.png");
define("FILENAME_IMAGE_SCHEME", "image_scheme.png");
define("FILENAME_IMAGE_QR_CODE", "qr_code.png");
define("FILENAME_PROJECT_XHTML", "project.xhtml");

// Misc filenames
define("PHP_SCRIPT_PRINT_QR_CODE", "print_qr_code.php");
define("FILENAME_PROJECTS_XML", "projects.xml");

// Project name minimum length
define("PROJECT_NAME_MINIMUM_LENGTH", 3);

// URL marker: Permanent download
define("URL_MARKER_DOWNLOAD", "d");

// URL marker: Temporary
define("URL_MARKER_TEMPORARY", "t");

// Defines for licenses
define("LICENSE_CC0_INDEX", 0);
define("LICENSE_CCBY_INDEX", 1);
define("LICENSE_CCBYSA_INDEX", 2);
define("LICENSE_CCBYNC_INDEX", 3);
define("LICENSE_CCBYND_INDEX", 4);
define("LICENSE_CCBYNCSA_INDEX", 5);
define("LICENSE_CCBYNCND_INDEX", 6);
define("LICENSE_CC0_TEXT", "Creative Commons: CC0 1.0 (no restrictions)");
define("LICENSE_CCBY_TEXT", "Creative Commons: CC BY 4.0");
define("LICENSE_CCBYSA_TEXT", "Creative Commons: CC BY-SA 4.0");
define("LICENSE_CCBYNC_TEXT", "Creative Commons: CC BY-NC 4.0");
define("LICENSE_CCBYND_TEXT", "Creative Commons: CC BY-ND 4.0");
define("LICENSE_CCBYNCSA_TEXT", "Creative Commons: CC BY-NC-SA 4.0");
define("LICENSE_CCBYNCND_TEXT", "Creative Commons: CC BY-NC-ND 4.0");

// Defines for tools
define("TOOL_LASERCUTTER", "Laser cutter");
define("TOOL_PCB_SOLDERING", "PCB / Soldering");
define("TOOL_3D_PRINTER", "3D printer");
define("TOOL_CNC_ROUTER", "CNC router");
define("TOOL_ARDUINO", "Arduino");
define("TOOL_RASPBERRY_PI", "Raspberry Pi");

// Seperators
define("TOOLS_SEPERATOR", ",");
define("REFERENCES_SEPERATOR", ",");

// Defines for not specified data
define("AUTHOR_NOT_SPECIFIED", "Not specified");
define("EMAIL_NOT_SPECIFIED", "Not specified");

// Display related stuff
define("COLON_TEXT", ": ");
define("REFERENCES_TEXT", "References");
define("REFERENCES_LINK_TEXT", "Link ");
define("REFERENCES_HTML_SEPERATOR", " - ");

// Indentation related stuff
define("GENERAL_INDENTATION", "    ");
define("TOOLS_MAIN_INDENTATION", 8);
define("REFERENCES_MAIN_INDENTATION", 9);

// Header settings
define("TITLE_SEPERATOR", " - ");
define("TITLE_PRINT_QR_CODE", "Print QR Code");
define("ICON_NAME", "icon.ico");

?>