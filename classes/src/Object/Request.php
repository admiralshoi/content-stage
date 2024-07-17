<?php

namespace classes\src\Object;

use classes\src\AbstractCrudObject;

class Request extends AbstractCrudObject {


    private array $requestOptions = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 1000,
        CURLOPT_HTTP_VERSION => "CURL_HTTP_VERSION_1_1",
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(),
        CURLOPT_FOLLOWLOCATION => true
    );
    private array $urlList = array();
    private array $results = array();
    private bool $multiCurl = false;
    private array $payload = array();




    //------------------------------------------------------------------------------------------------------------------------------------------

    public function initRequest(array $urls):void {
        $this->results = array();
        $this->urlList = $urls;
    }




    //------------------------------------------------------------------------------------------------------------------------------------------

    public function setPayload(array $payload):void { $this->payload = $payload; }


    //------------------------------------------------------------------------------------------------------------------------------------------


    public function sendRequest($curlOpt = array(), $method = "get", $extra = ""): string{
        if(empty($curlOpt)) $curlOpt = $this->requestOptions;
            if(strtolower($method) === "post" && !empty($this->payload)) $curlOpt[CURLOPT_POSTFIELDS] = $this->payload;

        $curlOpt[CURLOPT_CUSTOMREQUEST] = $method; $usage = "";

        foreach ($this->urlList as $url) {
            $curlOpt[CURLOPT_URL] = $url;
            $ch = curl_init();
            curl_setopt_array($ch,$curlOpt);
            $result = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($result, 0, $header_size);
            $headSplit = explode(PHP_EOL,$header);
            foreach ($headSplit as $head) {
                $usage = $head;
                if(strpos($head,"-usage") !== false) {
                    file_put_contents("testLogs/headerTest.txt",$extra . ": " .$head.PHP_EOL,8 | LOCK_EX);
                    break;
                }
            }

            $this->results[] = substr($result, $header_size);
            curl_close($ch);
        }

        return $usage;
    }



    //-------------------------------------------------------------------------------------------------------------------------------------------------


    public function sendHeaderRequest($curlOpt = array(), $method = "get"): void{
        if(empty($curlOpt)) $curlOpt = $this->requestOptions;
        $curlOpt[CURLOPT_CUSTOMREQUEST] = $method;

        foreach ($this->urlList as $url) {
            $curlOpt[CURLOPT_URL] = $url;
            $ch = curl_init();
            curl_setopt_array($ch,$curlOpt);
            $result = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $this->results[] = array("header" => substr($result, 0, $header_size), "body" => substr($result, $header_size));
            curl_close($ch);
        }
    }



    //------------------------------------------------------------------------------------------------------------------------------------------

    public function getResults(): array{
        return $this->results;
    }


    //------------------------------------------------------------------------------------------------------------------------------------------
}