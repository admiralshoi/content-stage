<?php
namespace classes\src\Tools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipHandler {

    public function create(string $sourceDirectory, string $destinationFile  = "./zipArchive.zip"): bool {
        if (!extension_loaded('zip') || !file_exists($sourceDirectory)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destinationFile, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $sourceDirectory = str_replace('\\', '/', realpath($sourceDirectory));

        if (is_dir($sourceDirectory) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectory), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true)
                {
                    $zip->addEmptyDir(str_replace($sourceDirectory . '/', '', $file . '/'));
                }
                else if (is_file($file) === true)
                {
                    $zip->addFromString(str_replace($sourceDirectory . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($sourceDirectory) === true)
        {
            $zip->addFromString(basename($sourceDirectory), file_get_contents($sourceDirectory));
        }

        return $zip->close();
    }

    public function createNoGetContents(string $sourceDirectory, string $destinationFile  = "./zipArchive.zip", string $rootBase = "/var/www/balanziapp.com/public_html/"): bool {
        if (!extension_loaded('zip') || !file_exists($sourceDirectory)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destinationFile, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $sourceDirectory = str_replace('\\', '/', realpath($sourceDirectory));

        if (is_dir($sourceDirectory) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectory), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
//                    $zip->addEmptyDir($dirName);
                }
                else if (is_file($file) === true) {
                    $zip->addFile($file);
                }
            }
        }
        else if (is_file($sourceDirectory) === true)
        {
            $fn = str_replace($rootBase, "", $sourceDirectory);
            $zip->addFile(basename($fn));
        }

        return $zip->close();
    }


    public function extraZip(string $sourceFile, string $destination): bool {
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




}