<?php

namespace Dotpay\Dotpay\Tool;

class Curl {
    private $_resource;
    private $_info;
    
    public function init()
    {
        $this->_resource = curl_init();
        
        return $this;
    }
    
    public function addCaInfo($file)
    {
        $this->addOption(CURLOPT_CAINFO, $file);
        
        return $this;
    }


    public function addOption($option, $value)
    {
        curl_setopt($this->_resource, $option, $value);
        return $this;
    }
    
    public function exec()
    {
        $response = curl_exec($this->_resource);
        $this->_info = curl_getinfo($this->_resource);
        
        return $response;
    }
    
    public function getInfo()
    {
        return $this->_info;
    }

    public function close()
    {
        curl_close($this->_resource);
    }
}