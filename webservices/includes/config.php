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

// URL marker: Permanent download
define("URL_MARKER_DOWNLOAD", "d");

// URL marker: Temporary
define("URL_MARKER_TEMPORARY", "t");

?>