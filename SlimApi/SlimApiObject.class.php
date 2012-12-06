<?php
/**
 * Base object for SlimApi
 * (c) 2012 Ondrej Podolinsky for Ataxo Interactive a.s.
 */
namespace SlimApi;

class SlimApiObject {
    protected $url;//address of slimapi services
    protected $version;//version of slimapi
    protected $taxonomy;//taxonomy-different for customers
    protected $apiToken;//apitoken-different for customers
    protected $findOptions;//limit for search
    protected $slimApiObjectName;//name of slim api object
    protected $apiUrl;//complete link on service included version, taxonomy and object name
    protected $findOptions;//limits for results
    protected $result;//complete result from api
    public function __construct($args) {
        //load predefined variables
        $this->url = array_key_exists('url', $args) ? $args['url'] : 'http://slimapi.ataxo.com';
        $this->version = array_key_exists('version', $args) ? $args['version'] : 'v1';
        $this->taxonomy = array_key_exists('taxonomy', $args) ? $args['taxonomy'] : 'sandbox';
        $this->apiToken = array_key_exists('apiToken', $args) ? $args['apiToken'] : 'apiToken';
        
        //set class name if it is not. Remove namespace.
        if(is_null($this->slimApiObjectName)){
            $name = explode('\\', get_class($this));
            $this->slimApiObjectName = strtolower($name[1]).'s';
        }
        //complete apiurl 
        $this->apiUrl = "{$this->url}/{$this->version}/{$this->taxonomy}/{$this->slimApiObjectName}";
        //predefined find
        $this->findOptions = array('limit' => 10, 'offset' => 0);
    }
    /**
     * Return all results limited findOptions variable
     * @param array $args
     * @return array
     */
    public function all($args = array()){
        $args['find'] = -1;//-1 for all results
        return $this->request('get',$args);
    }
    /**
     * Save object
     * @param type $args
     * @return array
     */
    public function save($args){
        return $this->request('post',$args);
    }
    /**
     * Delete object
     * @param type $args
     * @return array
     */
    public function delete($args){
        return $this->request('delete',$args);
    }
    /**
     * find results limited by args and findOptions variable
     * @param array $args
     * @return array
     */
    public function find($args){
        return $this->request($args);
    }
    /**
     * Main method for communication with api
     * @param string $method
     * @param array $args
     * @return array
     * @throws SlimApi\SlimApiException
     */
    protected function request($method = 'get',$args = null){
        $encodedArgs = json_encode($args);
        $ch = curl_init();
        if (!is_array($args))
            Throw new SlimApiException('Arguments for query missing.');
        
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedArgs);
        }
        
        elseif($method == 'put'){
            //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            $data = tmpfile();
            fwrite($data, $encodedArgs);
            fseek($data, 0);
            curl_setopt($ch, CURLOPT_INFILE, $data);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($encodedArgs));
            curl_setopt($ch, CURLOPT_PUT, 4);
        }
        elseif($method == 'delete'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $url = array_key_exists('id', $args) ? $this->apiUrl.'/'.$args['id'] : $this->apiUrl;
        
        if($method == 'get'){
            $url .= '?';
            $arr = array();
            foreach($this->findOptions as $key => $val)
                $arr[] .= urlencode($key).'='.urlencode($val);
            $url .= implode('&', $arr);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Api-Token: '.$this->apiToken, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "SlimApiPhpClient");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($ch));
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if(is_array($result))
            if($result['status'] != 'ok')
                Throw new SlimApiException("Call to URL {$url}[{$method}] failed: ".$result['error_type']." - ".$result['message']);
        else if ( $status != 201 && $status != 200 )
            Throw new SlimApiException("Call to URL {$url}[{$method}] failed: response: ".json_encode($result).",  http status: {$status}, curl_error: " . curl_error($ch) . ", curl_errno: " . curl_errno($ch));

        curl_close ($ch);
        $this->result = $result;
        $modelName = $this->slimApiObjectName;
        return $result->$modelName;
    }
    /**
     * Set limits for results
     * @param array $findOptions
     */
    public function setFindOptions($findOptions){
        $this->findOptions = $findOptions + $this->findOptions;
    }
    /**
     * Get limits for results
     * @return array
     */
    public function getFindOptions(){
        return $this->findOptions;
    }
    /**
     * Return complete request result from api
     * @return type
     */
    public function getResult(){
        return $this->result;
    }
}