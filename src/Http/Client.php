<?php

namespace RestInForce\Http;

class Client{

    private $guzzleClient;

    private $response;

    /**
    *	@param GuzzleHttpObject $guzzleClient     Guzzle HTTP Object
	**/
    public function __construct($guzzleClient){

        $this->guzzleClient = $guzzleClient;
        $this->baseUrl = $baseUrl;

    }


    /**
    *   Performs a request using Guzzle HTTP
    *	@param String $method       HTTP Methods (Post, Get, Patch)
    *   @param String $url          The URL of the request
    *   @param String $params       Guzzle HTTP params to perfom the request
	**/
    public function request($method, $url, $params = array()){

        $this->response = $this->guzzleClient->request($method,$url,$params);

        return $this;

    }

    /**
    *   Returns the response body
	**/
    public function getResposeBody(){
        
        if(!is_null($this->response->getBody())){
            return $this->response->getBody();
        }else{
            return NULL;
        }

    }

}