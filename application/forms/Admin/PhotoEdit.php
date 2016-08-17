<?php

class Application_Form_Admin_PhotoEdit extends Zend_Form
{
    public function init() {
       
        $title = new Zend_Form_Element_Text('title');
        //$firstName->addFilter(new Zend_Filter_StringTrim);
        //$firstName->addValidator(new Zend_Validate_StringLenght(array('min'=> 3, 'max'=> 255)));
        //
  
        //title
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(false);
        
        $this->addElement($title);
        
        //description
         $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(false);
        
        $this->addElement($description);

       
    }

    
    
}
