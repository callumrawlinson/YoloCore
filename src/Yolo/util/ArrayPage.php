<?php

namespace Yolo\util;

class ArrayPage {
    
    /** @var array */
    private $array;
    /** @var int */
    private $height;
    
    public function __construct(array $array, int $height) {
        $this->array = $array;
        $this->height = $height;
    }
    
    public function toArrayPage(int $page) : array {
        $array = array();
        $currentHeight = 0;
        $currentPage = 1;
        for ($i = 0; $i < count($this->array); $i++) {
            if ($this->height == $currentHeight) {
                $currentPage++;
                $currentHeight = 1;
            } else {
                $currentHeight++;
            }
            if ($page == $currentPage) {
                if ($this->height == $currentHeight) {
                    $array[] = $this->array[$i];
                    break;
                }
                $array[] = $this->array[$i];
            }
        }
        return $array;
    }
    
    /** @deprecated */
    public function yieldFromPage(int $page) {
        foreach ($this->toArrayPage($page) as $value) {
            yield $value;
        }
    }
    
    public function getMaxPages() {
        $page = 1;
        $currentHeight = 0;
        for ($i = 0; $i < count($this->array); $i++) {
            if ($this->height == $currentHeight) {
                $currentHeight = 1;
                $page++;
            } else {
                $currentHeight++;
            }
        }
        return $page;
    }
}