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
require_once("config.php");
require_once("phpqrcode/qrlib.php");
require_once("PHPMailer/PHPMailerAutoload.php");

// Function which sets error header and quits script execution
function quit_errorcode()
{
    // Set error header
    http_response_code(500);

    // Send error
    echo "Critical error!";

    // Stop script execution
    exit();
}

// Function to quit and remove project
function quit_removeproject_errorcode($projectId, $isPrivate)
{
    // Try to remove project
    remove_project($projectId, $isPrivate);

    // Quit with error code
    quit_errorcode();
}

// Function which tries to find a new unused project id and adds a new empty project with this id
function add_new_project($isPrivate, $projectName)
{
    // Variables
    $resultProjectId = "";
    $path = DIR_PUBLIC_PATH;
    $projectNameProcessed = "";

    // Process variables
    if (!empty($isPrivate))
    {
        $path = DIR_PRIVATE_PATH;
    }
    else
    {
        $projectNameProcessed = escape_and_encode(trim($projectName), "xml", "");
    }

    // Try multiple times to find a new project id
    for ($i = 0; $i < 20; $i++)
    {
        // Variable to store the result
        $newProjectId = "";

        // Length of a project id is 7 alphanumeric characters
        for ($j = 0; $j < 7; $j++)
        {
            // Random alphabetic character (26) or random number (10) => 36 possible entries
            $rand = rand(0, 35);

            // Numeric result
            if ($rand <= 9)
            {
                $newProjectId = $newProjectId . strval($rand);
            }
            // Alphabetic result
            else
            {
                // Shift range from 10 to 35 to range from 97 to 122 for ASCII characters a to z
                $newProjectId = $newProjectId . chr($rand + 87);
            }
        }

        // If directory does not exist, create it and return this project id
        if (!is_dir($path . $newProjectId))
        {
            // Check if creating new directory is successful
            if (mkdir($path . $newProjectId, 0770))
            {
                $resultProjectId = $newProjectId;
                break;
            }
        }
    }

    // Deal with index file
    $domDoc = new DOMDocument("1.0", "UTF-8");
    $domDoc->preserveWhiteSpace = false;
    $domDoc->formatOutput = true;

    // Open file descriptor
    $fileDescriptor = fopen($path . FILENAME_PROJECTS_XML, "a+");

    // Check if file opening is successful
    if ($fileDescriptor === false)
    {
        return "";
    }

    // Lock file, check if successful
    if (!flock($fileDescriptor, LOCK_EX))
    {
        return "";
    }

    // Try to load contents with warning suppression, warnings are allowed to happen here (e.g. new empty file)
    // In error case original contents are not changed anyways, so code can continue without any issues
    @$domDoc->load($path . FILENAME_PROJECTS_XML);

    // Check main node, if it does not exist, create it
    $indexNode = $domDoc->firstChild;

    if (empty($indexNode))
    {
        $indexNode = $domDoc->createElement("index");
        $indexNode->setAttribute("fablab-name", escape_and_encode(FABLAB_NAME, "xml", ""));
        $indexNode->setAttribute("url", escape_and_encode(((!empty($isPrivate)) ? PRIVATE_URL : PUBLIC_URL), "xml", ""));
        $indexNode->setAttribute("type", ((!empty($isPrivate)) ? "private" : "public"));
        $domDoc->appendChild($indexNode);
    }

    // Add project as first new child in index node
    $projectNode = $domDoc->createElement("project");
    $projectNode->setAttribute("id", $resultProjectId);
    $projectNode->setAttribute("name", $projectNameProcessed);
    $projectNode->setAttribute("create-timestamp", time());

    if (empty($indexNode->firstChild))
    {
        $indexNode->appendChild($projectNode);
    }
    else
    {
        $indexNode->insertBefore($projectNode, $indexNode->firstChild);
    }

    // Write contents to file
    if (file_put_contents($path . FILENAME_PROJECTS_XML, $domDoc->saveXML()) === false)
    {
        // Do not immediately return here, file lock is still set
        $resultProjectId = "";
    }

    // Unlock file
    if (!flock($fileDescriptor, LOCK_UN))
    {
        return "";
    }

    // Close file handle
    if (!fclose($fileDescriptor))
    {
        return "";
    }

    return $resultProjectId;
}

// Function which tries to remove a project
function remove_project($projectId, $isPrivate)
{
    // XML error variable, use to unlock file correctly
    $xmlError = false;

    // Set correct target path
    $path = DIR_PUBLIC_PATH;

    if (!empty($isPrivate))
    {
        $path = DIR_PRIVATE_PATH;

        // Remove QR code in public area
        if (file_exists(DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png"))
        {
            unlink(DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png");
        }
    }

    // If directory exists, remove entry in index and remove all files in it
    if (is_dir($path . $projectId))
    {
        // Deal with index file
        $domDoc = new DOMDocument("1.0", "UTF-8");
        $domDoc->preserveWhiteSpace = false;
        $domDoc->formatOutput = true;

        // Open file descriptor
        $fileDescriptor = fopen($path . FILENAME_PROJECTS_XML, "a+");

        // Check if file opening is successful
        if ($fileDescriptor === false)
        {
            return false;
        }

        // Lock file, check if successful
        if (!flock($fileDescriptor, LOCK_EX))
        {
            return false;
        }

        // Load contents, file must now be valid
        if (@$domDoc->load($path . FILENAME_PROJECTS_XML) === true)
        {
            // Get project node with project id from file, must be valid, use XPath
            $xpath = new DOMXPath($domDoc);
            $projectNode = $xpath->query("/index/project[@id='$projectId']")->item(0);

            if (!empty($projectNode) && !empty($projectNode->parentNode))
            {
                // Remove node in parent
                $projectNode->parentNode->removeChild($projectNode);

                // Write contents to file
                if (file_put_contents($path . FILENAME_PROJECTS_XML, $domDoc->saveXML()) === false)
                {
                    // Use XML error variable to abort execution after file was unlocked
                    $xmlError = true;
                }
            }
        }

        // Unlock file
        if (!flock($fileDescriptor, LOCK_UN))
        {
            return false;
        }

        // Close file handle
        if (!fclose($fileDescriptor))
        {
            return false;
        }

        // Directory has to be empty before it can be deleted, remove seperate files
        $files = scandir($path . $projectId);

        // Try to delete all files
        if (!empty($files))
        {
            foreach ($files as $file)
            {
                // Scandir also returns . and .. as relative paths to directories, ignore them in iteration
                if ($file == DIR_LOCAL || $file == DIR_PARENT)
                {
                    continue;
                }

                // For all other files, try to delete them
                unlink($path . $projectId . "/" . $file);
            }
        }

        // Check if removing directory is successful
        if (rmdir($path . $projectId) && empty($xmlError))
        {
            return true;
        }
    }

    return false;
}

// Function to generate QR codes to a specified file path
function generate_qr_code($text, $filepath)
{
    // Generate QR code with modified background color
    // QR code configs
    $pixelConfig = 11;
    $frameConfig = 3;
    $eclevelConfig = QR_ECLEVEL_M;

    // Color configs
    $bg_r = 229;
    $bg_g = 229;
    $bg_b = 229;
    $fg_r = 0;
    $fg_g = 0;
    $fg_b = 0;

    // Start processing
    $qrFrame = QRcode::text($text, false, $eclevelConfig);

    // Render image with GD
    $height = count($qrFrame);
    $width = strlen($qrFrame[0]);
    $imageWidth = $width + 2 * $frameConfig;
    $imageHeight = $height + 2 * $frameConfig;

    // Prepare base image
    $base_image = imagecreate($imageWidth, $imageHeight);
    $color_bg_base = imagecolorallocate($base_image, $bg_r, $bg_g, $bg_b);
    $color_fg_base = imagecolorallocate($base_image, $fg_r, $fg_g, $fg_b);

    // Base image with background color
    imagefill($base_image, 0, 0, $color_bg_base);

    // Iterate each pixel and color it with foreground color if needed by QR code
    for ($y = 0; $y < $height; $y++)
    {
        for ($x = 0; $x < $width; $x++)
        {
            if ($qrFrame[$y][$x] == '1')
            {
                imagesetpixel($base_image, $x + $frameConfig, $y + $frameConfig, $color_fg_base);
            }
        }
    }

    // Resize to final format
    $target_image = imagecreate($imageWidth * $pixelConfig, $imageHeight * $pixelConfig);
    imagecopyresized($target_image, $base_image, 0, 0, 0, 0, $imageWidth * $pixelConfig, $imageHeight * $pixelConfig, $imageWidth, $imageHeight);

    // Deallocate base image
    imagecolordeallocate($base_image, $color_bg_base);
    imagecolordeallocate($base_image, $color_fg_base);
    imagedestroy($base_image);

    // Draw 1 pixel border on QR code, for printing and cutting out the image
    $color_fg_target = imagecolorallocate($target_image, $fg_r, $fg_g, $fg_b);
    imagerectangle($target_image, 0, 0, ($imageWidth * $pixelConfig) - 1, ($imageHeight * $pixelConfig) - 1, $color_fg_target);

    // Save target image to filesystem and deallocate
    imagepng($target_image, $filepath);
    imagecolordeallocate($target_image, $color_fg_target);
    imagedestroy($target_image);

    // Check if file exists now
    if (!file_exists($filepath))
    {
        return false;
    }

    return true;
}

// Function to compute correct indentation hierachies
function indentation($count)
{
    $result = "";

    for ($i = 0; $i < $count; $i++)
    {
        $result = $result . GENERAL_INDENTATION;
    }

    return $result;
}

// Function to generate correct encoded / escaped values
function escape_and_encode($text, $mode, $linebreakReplacement)
{
    $result = "";

    switch ($mode)
    {
        case 'xml':
            $result = htmlspecialchars(strval($text), ENT_QUOTES | ENT_XML1, 'UTF-8');
            break;
        case 'xhtml':
            $result = htmlspecialchars(strval($text), ENT_QUOTES | ENT_XHTML, 'UTF-8');
            break;
        default:
            break;
    }

    $result = str_replace("\r\n", strval($linebreakReplacement), $text);
    $result = str_replace("\n\r", strval($linebreakReplacement), $text);
    $result = str_replace("\r", strval($linebreakReplacement), $text);
    $result = str_replace("\n", strval($linebreakReplacement), $text);

    return $result;
}

// Function to check if given projectId is syntactically correct
function is_project_id_syntax_valid($projectId)
{
    // Valid project ids are 7 characters long
    if (strlen(strval($projectId)) == 7)
    {
        for ($i = 0; $i < 7; $i++)
        {
            $asciiVal = ord($projectId[$i]);

            // 48 = 0, 122 = z, 57 = 9, 97 = a
            if ($asciiVal < 48 || $asciiVal > 122 || ($asciiVal > 57 && $asciiVal < 97))
            {
                return false;
            }
        }

        return true;
    }

    return false;
}

// Function to upload a file 
function upload_temporary_private_file($filesArray, $projectId)
{
    // Log attempt for upload
    $logFileName = "Unknown filename";

    if (!empty($filesArray["inputFile"]) && !empty($filesArray["inputFile"]["name"]))
    {
        $logFileName = $filesArray["inputFile"]["name"];
    }

    $logEntry = $_SERVER["REMOTE_ADDR"] . " -- [[" . date("Y/m/d H:i:s") . "]] -- \"POST " . "File Upload: " . $logFileName . "\"\n";
    if (file_put_contents(DIR_LOGS . LOGNAME_TEMPORARY_UPLOAD, $logEntry, FILE_APPEND) === false)
    {
        return "";
    }

    // Validate input: Files array
    if (empty($filesArray)
        || empty($filesArray["inputFile"]) || empty($filesArray["inputFile"]["tmp_name"]) || empty($filesArray["inputFile"]["size"]) || empty($filesArray["inputFile"]["name"])
        || !is_project_id_syntax_valid($projectId))
    {
        return "";
    }

    // Check file size
    if ($filesArray["inputFile"]["size"] > FILE_UPLOAD_MAXIMUM_SIZE_BYTES)
    {
        return "";
    }

    // Remove trailing whitespace
    $inputFileName = trim($filesArray["inputFile"]["name"]);

    // Seperate at dots in file name and use last part as file extension later on, for now only use first part
    $inputFileNameArray = explode(".", $inputFileName);
    $inputFileNameArrayLen = count($inputFileNameArray);

    // Invalid filename, need file name and file extension
    if ($inputFileNameArrayLen < 2)
    {
        return "";
    }

    $inputFileName = $inputFileNameArray[0];
    $inputFileExtension = strtolower($inputFileNameArray[$inputFileNameArrayLen - 1]);

    // Check if file extension is in allowed extensions
    $allowedExtensions = explode(",", FILE_UPLOAD_EXTENSIONS);
    $extensionAllowed = false;

    foreach ($allowedExtensions as $extension)
    {
        $extensionCleanup = str_replace(".", "", strtolower($extension));

        if ($inputFileExtension == $extensionCleanup)
        {
            $extensionAllowed = true;
            break;
        }
    }

    if (!$extensionAllowed)
    {
        return "";
    }

    // Replace whitespace with underscore
    $inputFileName = preg_replace("/\s/", "_", $inputFileName);

    // Special replacements for some characters
    $inputFileName = str_replace("Ä", "Ae", str_replace("Ö", "Oe", str_replace("Ü", "Ue", str_replace("ä", "ae", str_replace("ö", "oe", str_replace("ü", "ue", str_replace("ß", "ss", $inputFileName)))))));

    // Remove all non alphabetic / numeric or underscore or minus characters
    $inputFileName = preg_replace("/[^0-9a-zA-Z_\-]/", "", $inputFileName);

    // Append original input file type ending at end
    $inputFileName = $inputFileName . "." . $inputFileExtension;

    // Copy input file from temp directory to target file
    $inputFilePath = DIR_PRIVATE_PATH . $projectId . "/" . $inputFileName;

    if (!copy($_FILES["inputFile"]["tmp_name"], $inputFilePath))
    {
        return "";
    }

    // Generate QR code for that project
    $fileDownloadUrl = PRIVATE_URL . URL_MARKER_TEMPORARY . "/" . $projectId;
    $qrCodeFilePath = DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png";
    $qrCodeUrl = PUBLIC_URL . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png";

    if (!generate_qr_code($fileDownloadUrl, $qrCodeFilePath))
    {
        return "";
    }

    // Valid, return URL to PNG QR code image
    return $qrCodeUrl;
}

// Function to send emails, if projectId is not empty, the QR code will be attached
function send_email($recipientMail, $subject, $htmlBody, $plainBody, $projectId = "", $isPrivate = false)
{
    // Log attempt for email
    $logRecipientMail = "Unknown";
    $logSubject = "Unknown";
    $logProjectId = "Unknown";
    $logPrivate = "Unknown";

    if (!empty($recipientMail))
    {
        $logRecipientMail = $recipientMail;
    }
    
    if (!empty($subject))
    {
        $logSubject = $subject;
    }
    
    if (!empty($projectId))
    {
        $logProjectId = $projectId;
    }
    
    if (!empty($isPrivate))
    {
        $logPrivate = "true";
    }
    else
    {
        $logPrivate = "false";
    }

    $logEntry = $_SERVER["REMOTE_ADDR"] . " -- [[" . date("Y/m/d H:i:s") . "]] -- \"POST " . "Email: Recipient: '" . $logRecipientMail . "', Subject: '" . $logSubject . "', ProjectId: '" . $logProjectId . "', Private: '" . $logPrivate . "'\"\n";
    if (file_put_contents(DIR_LOGS . LOGNAME_EMAIL, $logEntry, FILE_APPEND) === false)
    {
        return "";
    }

    // Check validity
    if (empty($recipientMail) || empty($subject) || empty($htmlBody) || empty($plainBody))
    {
        return false;
    }

    // Check attachment
    $attachmentPath = "";

    if (!empty($projectId))
    {
        if (!is_project_id_syntax_valid($projectId))
        {
            return false;
        }

        if (empty($isPrivate))
        {
            $attachmentPath = DIR_PUBLIC_PATH . $projectId . "/" . FILENAME_IMAGE_QR_CODE;
        }
        else
        {
            $attachmentPath = DIR_PUBLIC_PATH . DIR_NAME_PRIVATE_QR_CODES . "/" . $projectId . ".png";
        }

        if (!file_exists($attachmentPath))
        {
            return false;
        }
    }

    // Setup email
    $email = new PHPMailer;
    $email->isSMTP();
    $email->SMTPAuth = true;
    $email->Host = SMTP_HOST;
    $email->Username = SMTP_USER;
    $email->Password = SMTP_PASSWORD;
    $email->SMTPSecure = SMTP_SECURE;
    $email->Port = SMTP_PORT;
    $email->Timeout = SMTP_TIMEOUT;
    $email->From = SMTP_MAIL;
    $email->FromName = FABLAB_NAME;
    $email->addAddress($recipientMail);

    // Add attachment, if specified and valid
    if (!empty($attachmentPath))
    {
        $email->addAttachment($attachmentPath, FILENAME_IMAGE_QR_CODE);
    }

    // Continue setup email
    $email->isHTML(true);
    $email->Subject = $subject;
    $email->Body = $htmlBody;
    $email->AltBody = $plainBody;

    // Try to send email
    if(!$email->send())
    {
        return false;
    }

    return true;
}

// Function to count elements in XML DOM documents
function count_projects_in_xml($isPrivate)
{
    // Result variable
    $result = 0;

    // Determine path of XML DOM document file
    $domPath = DIR_PUBLIC_PATH . FILENAME_PROJECTS_XML;

    if (!empty($isPrivate))
    {
        $domPath = DIR_PRIVATE_PATH . FILENAME_PROJECTS_XML;
    }

    // Check if file exists
    if (!file_exists($domPath))
    {
        return 0;
    }

    // Deal with index file
    $domDoc = new DOMDocument("1.0", "UTF-8");
    $domDoc->preserveWhiteSpace = false;
    $domDoc->formatOutput = true;

    // Open file descriptor
    $fileDescriptor = fopen($domPath, "a+");

    // Check if file opening is successful
    if ($fileDescriptor === false)
    {
        return 0;
    }

    // Lock file, check if successful
    if (!flock($fileDescriptor, LOCK_EX))
    {
        return 0;
    }

    // Try to load contents with warning suppression
    if (@$domDoc->load($domPath) == true)
    {
        // Get main node to count projects
        $indexNode = $domDoc->firstChild;

        if (!empty($indexNode))
        {
            // List of child nodes
            if (!empty($indexNode->childNodes))
            {
                $result = $indexNode->childNodes->length;
            }
        }
    }

    // Unlock file
    if (!flock($fileDescriptor, LOCK_UN))
    {
        return 0;
    }

    // Close file handle
    if (!fclose($fileDescriptor))
    {
        return 0;
    }

    return $result;
}

// Function to get latest project ids from XML DOM documents
function get_latest_projects_in_xml($count, $offset, $isPrivate)
{
    // Variables
    $result = array();
    $countClean = intval($count);
    $offsetClean = intval($offset);

    // Determine path of XML DOM document file
    $domPath = DIR_PUBLIC_PATH . FILENAME_PROJECTS_XML;

    if (!empty($isPrivate))
    {
        $domPath = DIR_PRIVATE_PATH . FILENAME_PROJECTS_XML;
    }

    // Check if file exists
    if (!file_exists($domPath))
    {
        return array();
    }

    // Deal with index file
    $domDoc = new DOMDocument("1.0", "UTF-8");
    $domDoc->preserveWhiteSpace = false;
    $domDoc->formatOutput = true;

    // Open file descriptor
    $fileDescriptor = fopen($domPath, "a+");

    // Check if file opening is successful
    if ($fileDescriptor === false)
    {
        return array();
    }

    // Lock file, check if successful
    if (!flock($fileDescriptor, LOCK_EX))
    {
        return array();
    }

    // Try to load contents with warning suppression
    if (@$domDoc->load($domPath) == true)
    {
        // Get main node to count projects
        $indexNode = $domDoc->firstChild;

        if (!empty($indexNode))
        {
            // List of child nodes
            if (!empty($indexNode->childNodes))
            {
                // Determine maximum index for iteration
                $maxCount = min($indexNode->childNodes->length, $offsetClean + $countClean);

                // Iterate project nodes
                for ($i = $offsetClean; $i < $maxCount; $i++)
                {
                    $projectNode = $indexNode->childNodes->item($i);

                    // Safely access id field of projectNode
                    if (!empty($projectNode))
                    {
                        $projectNodeAttributes = $projectNode->attributes;

                        if (!empty($projectNodeAttributes))
                        {
                            $projectNodeAttributesId = $projectNodeAttributes->getNamedItem("id");

                            if (!empty($projectNodeAttributesId))
                            {
                                $projectNodeAttributesIdValue = $projectNodeAttributesId->nodeValue;

                                if (!empty($projectNodeAttributesIdValue))
                                {
                                    $result[$i] = $projectNodeAttributesIdValue;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Unlock file
    if (!flock($fileDescriptor, LOCK_UN))
    {
        return array();
    }

    // Close file handle
    if (!fclose($fileDescriptor))
    {
        return array();
    }

    return $result;
}

?>