<?php

class Application_Model_Filter_UrlSlug implements Zend_Filter_Interface 
{
    public function filter($value) {
        
        //ovaj znak ^ znaci da se hvataju svi karaketri koji nisu navedeni i menjaju 
        //p{L} oznaka za sva slova
        //p{N} oznaka za sve brojeve
        $value = preg_replace('/[^\p{L}\p{N}]/u', '-', $value);
        $value = preg_replace('/(\s+)/', '-', $value);//jedan ili vise space-ova
        $value = preg_replace('/(\-+)/', '-', $value);//jedna ii vise -
        $value = trim($value, '-');
        
        return $value; 
        
    }
}