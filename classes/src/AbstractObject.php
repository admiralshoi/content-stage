<?php
namespace classes\src;

use JetBrains\PhpStorm\ArrayShape;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class AbstractObject {
    protected array $session;
    public $response;
    protected array $encode_keys = array(
        "iv" => 1111011011011101 ,// Non-NULL Initialization Vector for encryption
        "key" => 'DECHI_IMNDI_AOKDdjd_JOI',// Store the encryption key,
        "ciphering" => 'AES-128-CTR'// Store the cipher method
    );

    public const KNOWN_CONTENT_TYPES = [
        "image/jpeg" => "jpeg",
        "image/jpg" => "jpg",
        "image/png" => "png",
        "image/svn" => "svg",
        "image/webp" => "png",
        "image/gif" => "gif",
        "video/avi" => "avi",
        "video/mp4" => "mp4",
        "video/mov" => "mov",
        "video/wmv" => "wmv",
        "video/webm" => "mp4",
    ];

    function __construct(){
        $this->session = $_SESSION;
    }


    public function getResponse(){
        return $this->response;
    }

    public function grabDirContent($path,$flag = false, $fullPath = false){
        $content = $flag ? glob($path."*",GLOB_ONLYDIR) : glob($path."*");
        if(!empty($content)) {
            foreach ($content as $i => $item) {
                if(!$fullPath) $item = str_contains($item,$path) ? str_replace($path,"",$item) : basename($item);
                if($item === "." || $item === ".") unset($content[$i]);
                $content[$i] = $item;
            }
        }
        return $content;
    }

    public function publicError(int $code){
        $errors = array(
            2 => "No search query given",
            11 => "Something went wrong",
            12 => "Something went wrong",
            13 => "Something went wrong",
            101 => "Something went wrong",
            102 => "Something went wrong",
            103 => "Something went wrong",
            122 => "Something went wrong",
            123 => "Couldn't fetch request from the provider",
            131 => "Something went wrong",
            161 => "Couldn't fetch request from the provider",
            162 => "Couldn't fetch request from the provider",
            171 => "Something went wrong. Probably a bad search input caused this",
            172 => "Something went wrong",
            173 => "Couldn't fetch request from the provider",
            174 => "Couldn't fetch request from the provider",
            191 => "Couldn't fetch request from the provider",
            192 => "Couldn't fetch request from the provider",
            193 => "Couldn't fetch request from the provider",
            201 => "Too many search inquiries made. Wait a while before trying again",
            301 => "An unknown error occurred",
            899 => "Server error"
        );
        return array_key_exists($code,$errors) ? $errors[$code] : null;
    }

    public function serverLog(string $str, string|int $ref = "NaN", string $filePath = "logs/serverLogs.log"): void {
        $newLine = implode("  -  ", array(
            date("Y-m-d H:i:s"),
            "Ref: $ref",
            $str
        ));

        file_put_contents(ROOT . $filePath, $newLine . PHP_EOL, 8|2);
    }

    public function errorLog($error): bool {
        $append = "Date: ".date("d/m-Y H:i:s")."; ";
        $append .= isset($_SESSION["uid"]) ? "User: ".$_SESSION["uid"]."; " : "";
        if(is_array($error)) $append .= "Error_code: ".$error["error_code"]."; Error_msg: ".$error["error"];
        else $append .= $error;
        file_put_contents(ERR_LOG,$append . PHP_EOL,8|2);
        return true;
    }
    public function isAssoc(array $arr): bool {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }




    public function downloadMedia(
        $url,
        $dest,
        $content = null,
        $filename = "",
        bool $useExtension = true,
        bool $overwrite = false,
        bool $streamOpt = false
    ): bool|string {
        // Use basename() function to return the base name of file
        $filename = empty($filename) ? basename($url) : $filename;
        $fileInfoSource = $this->filenameInfo(basename($url));

        $outputFilename = empty($filename) ? $fileInfoSource["fn"] : ($useExtension ? "$filename." . $fileInfoSource["ext"] : $filename);
        if(str_contains($outputFilename, "?")) $outputFilename = explode("?", $outputFilename)[0];
        $path = $dest . $outputFilename;

        file_put_contents(TESTLOGS . "filenamelog.log", basename($path) . ", " . $outputFilename . PHP_EOL, 8);

        if(!$overwrite && file_exists($path)) return basename($path);
        if($content !== null && $content !== false) {
            try {
                $size = file_put_contents($path,$content);
                if((int)$size === 0) {
                    if(file_exists($path)) unlink($path);
                    return false;
                }
                return $outputFilename;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            // Gets the file from url and saves the file by using its base name
            try {
                if(!empty($streamOpt)) {
                    $content = $this->getFileAndHeaderFilename($url);
                    $fileData = $content["data"];
                    $responseHeaders = $content["headers"];
                    if(array_key_exists("filename", $responseHeaders)) $outputFilename = $responseHeaders["filename"];
                    else {
                        $contentType = $this->contentTypeFromHeaders($responseHeaders);
                        $ext = $this->extensionFromContentType($contentType);
                        if(empty($contentType) || empty($contentExt)) $ext = self::KNOWN_CONTENT_TYPES[(array_keys(self::KNOWN_CONTENT_TYPES)[0])];
                        $outputFilename = explode(".", $outputFilename)[0] . ".$ext";
                    }
                    $path = $dest . $outputFilename;
                }
                else $fileData = file_get_contents($url);


                $size = file_put_contents($path, $fileData);
                file_put_contents(TESTLOGS . "logfiles.log", "size: $size;   filename: " . $outputFilename . PHP_EOL . PHP_EOL, 8);

                if((int)$size === 0) {
                    if(file_exists($path)) unlink($path);
                    return false;
                }
                return basename($path);
            } catch (\Exception $e) {
                file_put_contents(TESTLOGS . "logfiles.log", $e . PHP_EOL . PHP_EOL, 8);
                return false;
            }
        }
    }


    #[ArrayShape(["data" => "false|string", "headers" => "array"])]
    public function getFileAndHeaderFilename(string $url): array {
        try {
            $streamOpt = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
                ]
            ];
            $context = stream_context_create($streamOpt);
            $this->multiArrayLog($streamOpt, "CONTEXT");
            $fileData = file_get_contents($url, false, $context);
            file_put_contents(TESTLOGS . "hooktra.log", strlen($fileData) . PHP_EOL, 8);
            $this->multiArrayLog($http_response_header, "someheaders");
            $responseHeaders = $this->cleanHttpResponseHeaders($http_response_header);
            $this->multiArrayLog($responseHeaders, "haddd");

            return [
                "data" => $fileData,
                "headers" => $responseHeaders
            ];
        }
        catch (\Exception $e) {
            file_put_contents(TESTLOGS . "hooktra.log", $e->getMessage() . PHP_EOL, 8);
        }
        return [
            "data" => $fileData,
            "headers" => $responseHeaders
        ];
    }

    public function contentTypeFromHeaders(array $headers): ?string {
        if(empty($headers)) return null;
        foreach (["Content-Type", "content-type", "ContentType"] as $key) {
            if(array_key_exists($key, $headers)) return $headers[$key];
        }
        return null;
    }
    public function extensionFromContentType(?string $contentType): ?string {
        if(empty($contentType)) return $contentType;
        return !array_key_exists(strtolower($contentType), self::KNOWN_CONTENT_TYPES) ? null : self::KNOWN_CONTENT_TYPES[strtolower($contentType)];
    }

    public function extToMediaType(?string $ext): ?string {
        $knownList = [
            "jpeg" => "image",
            "jpg" => "image",
            "gif" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "avi" => "video",
            "mp4" => "video",
            "mov" => "video",
            "wmv" => "video",
            "avchd" => "video",
            "webm," => "video",
            "flv," => "video"
        ];
        if(empty($ext)) return $ext;
        return !array_key_exists(strtolower($ext), $knownList) ? null : $knownList[strtolower($ext)];
    }


    #[ArrayShape(["ext" => "array|string|string[]", "fn" => "string", "fnid" => "array|string|string[]"])]
    public function filenameInfo($pathToFile): array {
        $filename = basename($pathToFile);

        if(strpos($filename,"?") !== false)
            $filename = (explode("?",$filename))[0];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        if($file_ext == false || $file_ext === "image")
            $file_ext = "png";
        if(strpos($filename,"~") !== false)
            $filename = (explode("~",$filename))[0].".".$file_ext;
        $name = str_replace(".".$file_ext,"",$filename);

        return array(
            "ext" => $file_ext,
            "fn" =>  $filename,
            "fnid" => $name
        );
    }


    public function encrypt(string $str, bool $decrypt = false): string {
        $iv_length = openssl_cipher_iv_length($this->encode_keys["ciphering"]); // Use OpenSSl Encryption method
        return $decrypt ? openssl_decrypt($str, $this->encode_keys["ciphering"], $this->encode_keys["key"], 0,$this->encode_keys["iv"])  //Decrypt
            : openssl_encrypt($str, $this->encode_keys["ciphering"], $this->encode_keys["key"], 0, $this->encode_keys["iv"]); //Encrypt
    }

    public function removeDirectory($directory, $removeStartDirectory = false): bool {
        if(!is_dir($directory)) return false;

        $it = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        if(!empty($files)) {
            foreach ($files as $file) {

                if($file->isDir()) rmdir($file->getRealPath());
                else unlink($file->getRealPath());
            }
        }
        if($removeStartDirectory) rmdir($directory);

        return !$removeStartDirectory || !is_dir($directory);
    }




    /**
     * Get header Authorization
     * */
    public function getAuthorizationHeader(): ?string {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     * */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }


    public function passwordHashing(string $password): string {
        if(empty($password)) return "";
        return hash("sha256", $this->encrypt($password));
    }




    public function multiArrayLog(array $content, ?string $key = null): void {
        $file = TESTLOGS . "multiArrLog.json";
        $currentContent = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        if($key !== null) $currentContent[$key] = $content;
        else $currentContent[] = $content;
        file_put_contents($file, json_encode($currentContent, JSON_PRETTY_PRINT));
    }




    public function encodeCursor(array $cursor): string {
        return base64_encode(
            $this->encrypt(
                json_encode($cursor)
            )
        );
    }

    public function decodeCursor(string $cursor): array {
        $data = json_decode(
            $this->encrypt(
                base64_decode($cursor),
                true
            )
        ,true);
        return is_array($data) ? $data : [];
    }


    public function cleanHttpResponseHeaders(array $headers): array {
        if(empty($headers)) return [];
        $collection = [];
        foreach ($headers as $header) {
            $split = explode(":", $header);
            $key = array_shift($split);
            $collection[$key] = trim(implode(":",$split));
        }

        if(array_key_exists("Content-Disposition", $collection)) {
            $disposition = $collection["Content-Disposition"];
            if(str_contains($disposition, "filename=")) {
                $split = explode(";", $disposition);
                foreach ($split as $str) {
                    if(!str_contains($str, "filename=")) continue;
                    $keyPair = explode("=", $disposition);
                    if(count($keyPair) > 1) {
                        $collection["filename"] = $keyPair[1];
                        break;
                    }
                }
            }
        }

        file_put_contents(TESTLOGS . "filehead.json", json_encode($collection, JSON_PRETTY_PRINT));
        return $collection;
    }

}