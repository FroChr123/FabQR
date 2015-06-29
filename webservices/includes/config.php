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
define("PUBLIC_URL", "http://CONFIGURE.ME:8090/");

// Private area URL
define("PRIVATE_URL", "http://CONFIGURE.ME:8081/");

// Name of the system, used in titles and headings
define("SYSTEM_NAME", "FabQR: Filesharing with QR Codes");

// Email config
define("SMTP_MAIL", "CONFIGURE-ME");
define("SMTP_HOST", "CONFIGURE-ME");
define("SMTP_PORT", "587");
define("SMTP_SECURE", "tls");
define("SMTP_USER", "CONFIGURE-ME");
define("SMTP_PASSWORD", "CONFIGURE-ME");

// Admin password config
define("ADMIN_PASSWORD", "CONFIGURE-ME");

/* **********************************
 * CONSTANTS, CAUTION IF CHANGED
 * ********************************** */

// Absolute directory path of public area
define("DIR_PUBLIC_PATH", "/home/fabqr/fabqr_data/www/public/");

// Absolute directory path of private area
define("DIR_PRIVATE_PATH", "/home/fabqr/fabqr_data/www/private/");

// Absolute directory path of logs area
define("DIR_LOGS", "/home/fabqr/fabqr_data/logs/");

// Seconds after which a temporary project is removed (2419200 = 4 weeks)
define("DELETE_TEMPORARY_FILE_INTERVAL", 2419200);

// Max elements per main site, related to fail2ban settings (requests per time)
define("PROJECTS_PER_SITE", 10);

// Misc file system paths and names
define("DIR_LOCAL", ".");
define("DIR_PARENT", "..");
define("DIR_NAME_PRIVATE_QR_CODES", "private_qr_codes");
define("PHP_SCRIPT_UPLOAD_FILE", "upload_file.php");
define("PHP_SCRIPT_PRINT_QR_CODE", "print_qr_code.php");
define("PHP_SCRIPT_EMAIL_QR_CODE", "email_qr_code.php");
define("PHP_SCRIPT_INDEX", "index.php");
define("PHP_SCRIPT_REMOVE_PROJECT", "remove_project.php");
define("FILENAME_PROJECTS_XML", "projects.xml");
define("LOGNAME_EMAIL", "fabqr_email.log");
define("LOGNAME_TEMPORARY_UPLOAD", "fabqr_temporary_upload.log");
define("ICON_NAME", "favicon.ico");
define("STYLE_NAME", "style.css");

// Filenames in project directories
define("FILENAME_IMAGE_REAL", "image_real.jpg");
define("FILENAME_IMAGE_SCHEME", "image_scheme.png");
define("FILENAME_IMAGE_QR_CODE", "qr_code.png");
define("FILENAME_PROJECT_XHTML", "project.xhtml");

// Configs for email
define("EMAIL_QR_CODE_SUBJECT_PREFIX", "QR Code - ");
define("EMAIL_QR_CODE_BODY", "This email contains your requested QR code as an attachment.");
define("SMTP_TIMEOUT", 30);

// Configs for file upload
define("FILE_UPLOAD_MAXIMUM_SIZE_BYTES", 10000000);
define("FILE_UPLOAD_EXTENSIONS", ".plf,.svg");

// Configs for QR codes
define("QR_CODE_PIXEL_MIN_PIXEL_SIZE", 348);
define("QR_CODE_FRAME_CONFIG", 3);
define("QR_CODE_COLOR_BG_R", 229);
define("QR_CODE_COLOR_BG_G", 229);
define("QR_CODE_COLOR_BG_B", 229);
define("QR_CODE_COLOR_FG_R", 0);
define("QR_CODE_COLOR_FG_G", 0);
define("QR_CODE_COLOR_FG_B", 0);

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
define("LICENSE_CC0_TEXT", "CC0 1.0 (no restrictions)");
define("LICENSE_CCBY_TEXT", "CC BY 4.0");
define("LICENSE_CCBYSA_TEXT", "CC BY-SA 4.0");
define("LICENSE_CCBYNC_TEXT", "CC BY-NC 4.0");
define("LICENSE_CCBYND_TEXT", "CC BY-ND 4.0");
define("LICENSE_CCBYNCSA_TEXT", "CC BY-NC-SA 4.0");
define("LICENSE_CCBYNCND_TEXT", "CC BY-NC-ND 4.0");

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
define("TOOLS_MAIN_INDENTATION", 7);
define("REFERENCES_MAIN_INDENTATION", 9);

// Title settings
define("TITLE_SEPERATOR", " - ");
define("TITLE_PRINT_QR_CODE", "Print QR Code");
define("TITLE_EMAIL_QR_CODE", "Email QR Code");
define("TITLE_UPLOAD_FILE", "File Upload");
define("TITLE_REMOVE_PROJECT", "Remove Project");

?>