<?php

namespace RestInForce\Rest;

class Client{

    private $httpClient;

    private $accessToken;

    private $baseUrl;

    private $basePath = "/services/data/";

    private $apiVersion = "v42.0";

    private $nextRecordsUrl;

    private $query;

    /**
    *   @param GuzzleHttpObject $httpClient     Guzzle HTTP Object
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
    *   Performs a SOQL Query and loop to retrive all paginated results
    *   so the best pratice is use LIMIT in your query
    *   @param String $query       SOQL Query string
    **/
    public function query($query){

        $this->query = $query;

        $url = $this->baseUrl . '/services/data/'.$this->apiVersion.'/query/?q=' . urlencode($this->query);

        $params = [
            'headers' => [
                "Authorization"=> "Bearer ".$this->accessToken,
            ],
            "query"=>[
                "q"=>$this->query
            ],
            "debug"=>false,
        ];

        $response = $this->postQuery($url, $params);

        $results = $response['records'];

        while(!$response['done']){

            $nextRequest = $response['nextRecordsUrl'];

            if (!empty($nextRequest)){

                $url = $this->baseUrl . '/' . $nextRequest;

                $response = $this->postQuery($url, $params);

                $results = array_merge($results,$response['records']);

            }

        }

        return $results;

    }

    /**
    *   Performs a single SOQL Query.
    *   Be careful, if salesforce paginate the result you will 
    *   need call nextRecordMethods to retrieve the data.
    *   @param String $query       SOQL Query string
    **/
    public function singleQuery($query){

        $this->query = $query;

        $url = $this->baseUrl . '/services/data/'.$this->apiVersion.'/query/?q=' . urlencode($this->query);

        $params = [
            'headers' => [
                "Authorization"=> "Bearer ".$this->accessToken,
            ],
            "query"=>[
                "q"=>$this->query
            ],
            "debug"=>false,
        ];

        $response = $this->postQuery($url, $params);
        $results = $response['records'];

        if(array_key_exists("nextRecordsUrl",$response)){

            $this->nextRecordsUrl = $this->baseUrl . '/' . $response['nextRecordsUrl'];
            $done = "false";          

        }else{

            $this->nextRecordsUrl = null;
            $done = "true";

        }

        return array("results"=>$results,"done"=>$done);

    }


    /**
    *   Performs a SOQL Query for the next page result.
    *   This method only will work after you perform SingleQuery method
    *   and the result be paginated by Salesforce.
    **/
    public function nextRecordsQuery(){

        $params = [
            'headers' => [
                "Authorization"=> "Bearer ".$this->accessToken,
            ],
            "query"=>[
                "q"=>$this->query
            ],
            "debug"=>false,
        ];

        $response = $this->postQuery($this->nextRecordsUrl, $params);
        $results = $response['records'];
        
        if(array_key_exists("nextRecordsUrl",$response)){

            $this->nextRecordsUrl = $this->baseUrl . '/' . $response['nextRecordsUrl'];
            $done = "false";          

        }else{

            $this->nextRecordsUrl = null;
            $done = "true";

        }

        return array("results"=>$results,"done"=>$done);

    }

    /**
    *   Performs a SOQL Query for the next page result.
    *   @param String $url       Salesforce URL that will be posted.
    *   @param String $params    Parameters used on post.
    **/
    public function postQuery($url, $params){

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
    *   Retreive all objects, full version.
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
    *   Retreive all objects names.
    **/
    public function getAllObjectsNames(){

        $objectsList = $this->getAllObjects();


        foreach($objectsList["sobjects"] as $object){
            $fieldsArray[] = array("label"=>$object["label"],"name"=>$object["name"], "queryable"=>$object["queryable"]);
        }


        return $fieldsArray;

    }

}