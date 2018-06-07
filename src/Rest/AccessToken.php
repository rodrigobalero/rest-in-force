<?php

namespace RestInForce\Rest;

class AccessToken{

    private $grantType;

    private $clientId;

    private $clientSecret;

    private $username;

    private $password;

    private $securityToken;

    private $baseUrl;

    private $httpClient;

    /**
    *	@param GuzzleHttpObject $httpClient     Guzzle HTTP Object
    *   @param String $clientId                 Salesforce APP Client ID
    *   @param String $clientSecret             SalesForce APP Client Secret
    *   @param String $username                 SalesForce username
    *   @param String $password                 SalesForce password
    *   @param String $securityToken            SalesForce user security token
    *   @param String $grantType                API grant type
	**/
    public function __construct($httpClient, $clientId, $clientSecret, $username, $password, $securityToken, $baseUrl, $grantType = "password"){

        $this->httpClient = $httpClient;
        
        $this->clientId = $clientId;

        $this->clientSecret = $clientSecret;

        $this->username = $username;

        $this->password = $password;

        $this->securityToken = $securityToken;

        $this->baseUrl = $baseUrl;

        $this->grantType = $grantType;

    }

    /**
    *   Request the oath2 token
	**/
    public function requestToken(){

        $passwordSecurityToken = $this->password.$this->securityToken;

        $oauthUrl = $this->baseUrl."/services/oauth2/token";

        $params = [
            "query"=>[
                "grant_type"=>$this->grantType,
                "client_id"=>$this->clientId,
                "client_secret"=>$this->clientSecret,
                "username"=>$this->username,
                "password"=>$passwordSecurityToken
            ]
        ];

        $response = json_decode($this->httpClient->request("POST",$oauthUrl,$params)->getResposeBody(),true);

        return $response;

    }

}