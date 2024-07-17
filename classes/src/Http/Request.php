<?php

namespace classes\src\Http;

use classes\src\AbstractCrudObject;
use classes\src\Object\CookieManager;
use JetBrains\PhpStorm\Pure;

class Request {
    private array $defaultHeaders =  [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
        'X-Ig-App-Id: 936619743392459',
    ];




    private array $headers =  [];
    private string|array $postParams;
    private mixed $response = null;
    private ?array $error = null;
    private bool $postingUrlEncoded = false;

    function __construct(string|array $postParams = array(), array $headers = array()) {
        $this->postParams = $postParams;
        if(!empty($headers)) $this->headers = $headers;
        else $this->headers = $this->defaultHeaders;
    }

    public function setDefaultHeaders(): void { $this->headers = $this->defaultHeaders; }
    public function setBody(string|array $postParams): void { $this->postParams = $postParams; }
    public function setHeaders(array $headers): void { $this->headers = $headers; }
    public function addHeader(string $value, string $key = ""): void { if(!empty($key)) {$this->headers[$key] = $value; } else {$this->headers[] = $value;} }
    public function setUserPwd(string $username, string $password): void { $this->addHeader("Authorization: Basic " . base64_encode("$username:$password")); }
    public function setBearerToken(string $token): void { $this->addHeader("Authorization: Bearer $token"); }
    public function setPostUrlEncode(): void {$this->postingUrlEncoded = true; }
    public function removePostUrlEncode(array $params): void {$this->postingUrlEncoded = false; }
    public function setHeaderContentTypeJson(): void { $this->addHeader('Content-type: application/json; charset=UTF-8', "content_type"); }

    public function send(array|string $url, string $requestType = "GET"): void {
        $this->response = null;
        if($this->postingUrlEncoded && in_array(strtolower($requestType), ["POST", "DELETE"]))
            $this->addHeader('Content-type: application/x-www-form-urlencoded', "content_type");

        $curlOpt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 500,
            CURLOPT_HTTP_VERSION => "CURL_HTTP_VERSION_1_1",
            CURLOPT_CUSTOMREQUEST => $requestType,
            CURLOPT_HTTPHEADER => array_values($this->headers),
            CURLOPT_FOLLOWLOCATION => 1
        );

        try {
            if(is_array($url)) {
                $mh = curl_multi_init();
                $result = $curlHandles = array();

                foreach ($url as $uri) {
                    $ch = curl_init();

                    if(is_array($uri)) {
                        $requestUrl = $uri["url"];
                        if(in_array($requestType, ["POST", "DELETE"])) $curlOpt[CURLOPT_POSTFIELDS] = $this->postingUrlEncoded ? http_build_query($uri["payload"]) : $uri["payload"];
                    } else {
                        $requestUrl = $uri;
                        if(in_array($requestType, ["POST", "DELETE"])) $curlOpt[CURLOPT_POSTFIELDS] = $this->postingUrlEncoded ? http_build_query($uri["payload"]) : $uri["payload"];
                    }

                    $curlOpt[CURLOPT_URL] = $requestUrl;
                    curl_setopt_array($ch,$curlOpt);
                    curl_multi_add_handle($mh, $ch);
                    $curlHandles[] = $ch;
                }

                do { //Current max 20 at once
                    curl_multi_exec($mh,$running);
                    curl_multi_select($mh);
                } while($running > 0);

                foreach ($curlHandles as $handle) {
                    $pageResult = curl_multi_getcontent( $handle );

                    curl_multi_remove_handle($mh,$handle);
                    curl_close($handle);

                    $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
                    $header = substr($pageResult, 0, $header_size);
                    $result[] = substr($pageResult, $header_size);
                }

                $this->response = $result;
                curl_multi_close($mh);
            }
            else {
                $curlOpt[CURLOPT_URL] = $url;
                if(in_array($requestType, ["POST", "DELETE"])) $curlOpt[CURLOPT_POSTFIELDS] = $this->postingUrlEncoded ? http_build_query($this->postParams) : $this->postParams;

                file_put_contents("testLogs/reqSend.json", json_encode($curlOpt, JSON_PRETTY_PRINT));

                $ch = curl_init();
                curl_setopt_array($ch,$curlOpt);
                $res = curl_exec($ch);
                curl_close($ch);

                file_put_contents( "testLogs/curlres.txt", $res);

                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($res, 0, $header_size);

                $this->response = substr($res, $header_size);
                file_put_contents("testLogs/testmepostpostHeader.txt", $header);
            }

        } catch (\Exception $exception) {
            $this->error = array("error" => "Failed curl request", "error_message" => $exception, "error_code" => 101);
        }
    }

    public function getResponse(bool $clean = true): mixed {
        if(!is_array($this->response)) {
            $res = $this->response;
            return $clean && !empty($res) && !is_array($res) ? json_decode($res, true) : $res;
        }

        $res = [];
        foreach ($this->response as $item) $res[] = $clean && !empty($item) && !is_array($item) ? json_decode($item, true) : $item;
        return $res;
    }


    public function clearResponse(): void {$this->error = null; $this->response = null; }
    public function getError(): ?array {return $this->error; }
    #[Pure] public function getErrorCode(): ?int {return array_key_exists("error_code", $this->getError()) ? $this->getError()["error_code"] : null; }
    #[Pure] public function getErrorTitle(): ?int {return array_key_exists("error", $this->getError()) ? $this->getError()["error"] : null; }
    #[Pure] public function getErrorMessage(): ?int {return array_key_exists("error_message", $this->getError()) ? $this->getError()["error_message"] : null; }
    #[Pure] public function isError(): bool {return $this->getError() !== null;}





    /* COOKIE MANAGER */

    protected CookieManager $cookieManager;
    public function setCookieManager(AbstractCrudObject $crud): static {
        $this->cookieManager = new CookieManager($crud);
        return $this;
    }

    private function cookieGet(): string { return $this->cookieManager->cookieGet(); }
    #[Pure] public function remainingUnusedCookies(): int { return $this->cookieManager->remainingUnusedCookies(); }
    #[Pure] public function isUnusedCookies(): bool { return $this->cookieManager->isUnusedCookies(); }
    public function cookieSet(): static { $this->cookieManager->cookieSet(); return $this; }
    public function cookieInvalidate(): static { $this->cookieManager->cookieInvalidate(); return $this; }
    public function cookieUsageIncrement(bool $success = true): static { $this->cookieManager->cookieUsageIncrement($success); return $this; }
    public function cookieSetDefault(): static { $this->cookieManager->cookieSetDefault(); return $this; }

    public function cookieAddToHeader(): static {
        $this->headers["cookie"] = $this->cookieGet();
        file_put_contents(TESTLOGS . "header-cookie", implode(PHP_EOL, $this->headers) . PHP_EOL . PHP_EOL, 8);
        return $this;
    }
    public function getCurrentCookie(): string {
        return array_key_exists("cookie", $this->headers) ? $this->headers["cookie"] : "";
    }
    public function cookieManualAddToHeader(string $cookie): static {
        $this->headers["cookie"] = $cookie;
        return $this;
    }
    public function cookieRemoveFromHeader(): static {if(isset($this->headers["cookie"])) unset($this->headers["cookie"]);  return $this; }

}