<?php
class Zend_View_Helper_ContactUrl extends Zend_View_Helper_Abstract
{
 
     public function contactUrl($member){
        
        return $this->view->url(array(
            'id'=>$member['id'],
            'member_slug'=>$member['first_name'] . '-' . $member['last_name']
            
        ), 'ask-member-route', true);
       
    }
}