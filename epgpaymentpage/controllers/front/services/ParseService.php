<?php

class ParseService
{
    private $rawData;

    public function __construct($rawData)
    {
        $this->rawData = $rawData;
    }

    public function parse()
    {
        return json_decode($this->rawData);
    }
}