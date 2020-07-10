<?php

namespace App\Helpers;

use ParseCsv\Csv;

class CsvHelper {

    /**
     * Filename of the csv file
     * @var
     */
    protected $filename;

    /**
     * The path where the csv file is uploaded
     * @var
     */
    protected $path;

    /**
     * The csv instance
     *
     * @var
     */
    protected $csv;

    /**
     * The data returned from the csv
     *
     * @var array
     */
    protected $data;

    /**
     * Csv header fields
     *
     * @var
     */
    protected $header_fields;

    /**
     * Allowed formats
     *
     * @var array
     */
    protected $allows = [
        'csv',
        'xls',
        'xlsx'
    ];



    /**
     * Set the path
     *
     * @param $path
     * @return $this
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the filename
     *
     * @param $filename
     * @return $this
     */
    public function setFileName($filename) {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the specified path
     *
     * @return mixed
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Get filename
     *
     * @return mixed
     */
    public function getFileName() {
        return $this->filename;
    }

    /**
     * Get CSV instance
     *
     * @return mixed
     */
    public function getCSV() {
        return $this->csv;
    }

    /**
     * Get the parsed csv data
     *
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Process the csv
     *
     * @return $this
     */
    public function parse() {

        // First detect the character encoding
        $allEncodings = mb_list_encodings();
        $fd = fopen($this->path,'r');
        $row = fgets($fd);
        $encoding = mb_detect_encoding($row, $allEncodings);
        fclose($fd);

        // set csv instance
        $this->csv = new Csv($this->path);

        // set detected encoding on the csv parser
        $this->csv->encoding($encoding, $encoding);

        // auto detect delimiter
        $this->csv->auto();

        // get data
        $this->data = $this->csv->data;

        return $this;
    }


    /**
     * Create a csv file
     *
     * @param $data
     */
    public function create($data) {
        $csv = new Csv;

        // set paths
        $paths = [$this->path, $this->filename];

        // generate path
        $full_path = $this->path;

        $res = $csv->save($full_path, $data, true);

        return $res;
    }

    /**
     * Helpers
     */

    /**
     * Returns the allowed formats
     *
     * @return array
     */
    public function getAllowedFormats() {
        return $this->allows;
    }

    /**
     * Get the header fields
     *
     * @return array
     */
    public function getHeaderFields() {
        $res = [];

        if ($this->data) {
            $arr = $this->data[0];
            $res = array_keys($arr);
        }

        return $res;
    }
}