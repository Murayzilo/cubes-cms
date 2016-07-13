<?php

class Application_Form_Admin_MemberAdd extends Zend_Form
{
    public function init() {
       
        $firstName = new Zend_Form_Element_Text('first_name');
        //$firstName->addFilter(new Zend_Filter_StringTrim);
        //$firstName->addValidator(new Zend_Validate_StringLenght(array('min'=> 3, 'max'=> 255)));
        //
  
        //first name
        $firstName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(true);
        
        $this->addElement($firstName);
        //last name
        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(true);
        
        $this->addElement($lastName);
        //work title
        $workTitle = new Zend_Form_Element_Text('work_title');

        $workTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(false);
        
        $this->addElement($workTitle);
        
        //email
        $email = new Zend_Form_Element_Text('email');

        $email->addFilter('StringTrim')
                ->addValidator('EmailAddress', false, array('domain' => false))
                ->setRequired(true);
        
        $this->addElement($email);
        
        //resume
        $resume = new Zend_Form_Element_Textarea('resume');
        $resume->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($resume);
        
        
        }

    
    
}
