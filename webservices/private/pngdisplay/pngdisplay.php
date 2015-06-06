<?php

// Load configuration
require_once("config.php");

// Get FILES data, PNG bytes
if (!empty($_FILES) && !empty($_FILES["data"]) && !empty($_FILES["data"]["tmp_name"]) && !empty($_FILES["data"]["size"]))
{
    // Open file handle, create file if does not exist, do not truncate file (as "w" would do)
    $file = fopen(PNG_FILE_PATH, "c");

    // Check if file opening is successful
    if ($file !== false)
    {
        // Lock file, check if successful
        if (flock($file, LOCK_EX))
        {
            // Copy file from temp directory to real file
            copy($_FILES["data"]["tmp_name"], PNG_FILE_PATH);

            // Unlock file
            flock($file, LOCK_UN);
        }

        // Close file handle
        fclose($file);
    }
}

?>