<?php

class Application_Form_Admin_UserEdit extends Zend_Form
{
    
    protected $editUserId;
    
    public function __construct($editUserId,$options = null) { // $options = null  - ako parametar ima default vrednost onda ne moramo da ga pozivamo
        
        if (empty ($editUserId)) {
            throw new InvalidArgumentException('Edited user id can not be empty');
        }
        
        $this->editUserId = $editUserId;
        
        parent::__construct($options);
    }

    
    public function init() {
        
        //user name
        $username = new Zend_Form_Element_Text('username');
        $username->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 5, 'max'=> 50))
                ->addValidator(new Zend_Validate_Db_NoRecordExists(
                        array(
                    'table' => 'cms_users',
                    'field' => 'username',
                    'exclude' => array(
                    'field' => 'id',
                    'value' => $this->editUserId
                    )
                )))
                ->setRequired(true);
        
        $this->addElement($username);
       
        $firstName = new Zend_Form_Element_Text('first_name');
        //$firstName->addFilter(new Zend_Filter_StringTrim);
        //$firstName->addValidator(new Zend_Validate_StringLenght(array('min'=> 3, 'max'=> 255)));
        //
  
        //first name
        $firstName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(false);
        
        $this->addElement($firstName);
        
        //last name
        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(false);
        
        $this->addElement($lastName);
        
        //email
        $email = new Zend_Form_Element_Text('email');

        $email->addFilter('StringTrim')
                ->addValidator('EmailAddress', false, array('domain' => false))
                ->setRequired(false);
        
        $this->addElement($email);

    }

    
    
}
