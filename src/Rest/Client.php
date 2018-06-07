<?php

namespace RestInForce\Rest;

class Client{

    private $httpClient;

    private $accessToken;

    private $baseUrl;

    private $basePath = "/services/data/";

    private $apiVersion = "v42.0";

    /**
    *	@param GuzzleHttpObject $httpClient     Guzzle HTTP Object
    *   @param String $baseUrl      SalesForce Base URL
	**/
    public function __construct($httpClient, $baseUrl){

        $this->httpClient = $httpClient;

        $this->baseUrl = $baseUrl;

    }

    /**
    *   Set the access token
    *   @param String $accessToken      Access Token
    **/
    public function auth($accessToken){

        $this->accessToken = $accessToken;

    }

    /**
    *   Return the URL with the path of the method
    *   @param String $methodPath       API Method
    **/
    private function getUrl($methodPath){

        return $this->baseUrl.$this->basePath.$this->apiVersion.$methodPath;

    }

    /**
    *   Performs a SOQL Query
    *   @param String $query       SOQL Query string
    **/
    public function query($query){

        $url = $this->getUrl("/query/");

        $params = [
            'headers' => [
                "Authorization"=> "Bearer ".$this->accessToken,
            ],
            "query"=>[
                "q"=>$query
            ],
            "debug"=>false,
        ];

        $response = json_decode($this->httpClient->request("GET",$url,$params)->getResposeBody(),true);

        return $response;

    }

    /**
    *   Retreive the description of an object.
    *   @param String $object       Object Name
    **/
    private function describe($object){

        $url = $this->getUrl("/sobjects/".$object."/describe/");

        $params = [
            'headers' => [
                "Authorization"=> "Bearer ".$this->accessToken,
            ],
            "debug"=>false,
        ];

        $response = json_decode($this->httpClient->request("GET",$url,$params)->getResposeBody(),true);

        return $response;

    }

    /**
    *   Retreive the description of an object.
    *   @param String $object       Object Name
    **/
    public function getObjectFieldsNames($object){

        $describeResult = $this->describe($object);


        foreach($describeResult["fields"] as $fields){
            $fieldsArray[] = array("label"=>$fields["label"],"name"=>$fields["name"], "type"=>$fields["type"]);
        }


        return $fieldsArray;

    }


    /**
    *   Retreive all objects.
    **/
    public function getAllObjects(){

        $url = $this->getUrl("/sobjects/");

        $params = [
            'headers' => [
                "Authorization"=> "Bearer ".$this->accessToken,
            ],
            "debug"=>false,
        ];

        $response = json_decode($this->httpClient->request("GET",$url,$params)->getResposeBody(),true);

        return $response;

    }


    /**
    *   Retreive all objects.
    **/
    public function getAllObjectsNames(){

        $objectsList = $this->getAllObjects($object);


        foreach($objectsList["sobjects"] as $object){
            $fieldsArray[] = array("label"=>$object["label"],"name"=>$object["name"], "queryable"=>$object["queryable"]);
        }


        return $fieldsArray;

    }

}