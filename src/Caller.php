<?php

namespace App;

class Caller
{

    private $curl;
    private $sort = false;
    private $sortFields = [];
    private $userAgent = 'mccubbin';

    /**
     * initialize curl object
     */
    public function __construct() {
        $this->curl = curl_init();
    }

    /**
     * makes a GET or POST call
     */
    public function make($url, $method, $data = null) {

        // immediately save URL to class variable
        $this->url = $url;

        // change curl options based on type of call we wish to make
        switch ($method) {
            case "get":
                // do nothing
                break;
            case "post":
                curl_setopt($this->curl, CURLOPT_POST, 1);

                if ($data) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
        }

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);

        return $this;
    }

    /**
     * no need for this method, we are already at the root position
     */
    public function root() {
        return $this;
    }

    /**
     * append predicates to URL's query string
     */
    public function where($field, $comparison, $value) {

        // add either ? or &
        $append = '';
        if (substr($this->url, -1) != '&') {
            $append .= '?';
        }

        // change bool value to string
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $append .= $field . $comparison . $value . '&';
        $this->url .= $append;

        return $this;
    }

    /**
     * this api does not appear to have any sort params
     * so we sort the results manually
     */
    public function sort($column, $dir = 'ASC') {
        $this->sort = true;
        array_push($this->sortFields, [$column, $dir]);

        return $this;
    }

    /**
     * sort json array manually using sortFields array values
     */
    public function sortArray($results) {
        $array = json_decode($results, true);

        // get sortFields and sort according to each
        foreach ($this->sortFields as $sort) {
            $column = $sort[0];
            $dir = $sort[1];

            // sort ascending or descending based on param
            if ($dir == "ASC") {
                usort($array, function ($a, $b) use ($column) {
                    return $a[$column] <=> $b[$column];
                });
            } else {
                usort($array, function ($a, $b) use ($column) {
                    return $b[$column] <=> $a[$column];
                });
            }

        }

        return json_encode($array);
    }

    /**
     * with results, format them and print them in the console
     */
    public function get() {
        $results = $this->fetchResults();

        // format json for console
        $results = json_decode($results);
        $results = json_encode($results, JSON_PRETTY_PRINT);
        echo $results;
    }

    /**
     * similar to get(), except only the passed in columns are displayed
     */
    public function only($columns = []) {
        $results = $this->fetchResults();

        // cherry pick columns based on incoming array
        $array = json_decode($results, true);
        $newArray = [];

        foreach ($array as $row) {
            $newRow = [];
            foreach ($columns as $column) {
                $newRow[$column] = $row[$column];
            }
            array_push($newArray, $newRow);
        }

        // format json for console
        $results = json_encode($newArray, JSON_PRETTY_PRINT);
        echo $results;
    }


    /**
     * make API call and sort according to sortArray
     */
    public function fetchResults() {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);

        $results = curl_exec($this->curl);
        curl_close($this->curl);

        if ($this->sort == true) {
            $results = $this->sortArray($results);
        }

        return $results;
    }
}

