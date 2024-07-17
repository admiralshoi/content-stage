<?php
function extraZip(string $sourceFile, string $destination): bool {
    if(!file_exists($sourceFile)) return false;
    if(!file_exists($destination) || !is_dir($destination)) return false;


    $zip = new ZipArchive;
    $res = $zip->open($sourceFile);

    if ($res) {
        $zip->extractTo($destination);

        return $zip->close();
    }

    return false;
}


extraZip($_SERVER["DOCUMENT_ROOT"] . "/goodbrandslove.zip", $_SERVER["DOCUMENT_ROOT"] . "/");


