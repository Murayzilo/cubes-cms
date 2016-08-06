<?php

class Application_Form_Admin_ClientAdd extends Zend_Form
{
    public function init() {
        
        //client name
        $name = new Zend_Form_Element_Text('name');
        $name->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(true);        
        $this->addElement($name);
       
        //description
        $description = new Zend_Form_Element_Text('description');
       
        $description->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255)) 
                    ->setRequired(false);
        $this->addElement($description);
        
   //client Photography
        $clientPhoto = new Zend_Form_Element_File('client_photo');
        
        $clientPhoto->addValidator('Count', true, 1)// ogranicen broj fajlova koji se mogu upload-ovati na 1
                    ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))// true prekida izvrsenje naredbe, a false ne prekida (prilikom validacije) NA NIVOU ELEMENTA
                    ->addValidator('ImageSize', false, array(
                        'minwidth' => 70,
                        'minheight' => 170,
                        'maxwidth' => 2000,
                        'maxheight' => 2000
                    ))
                     ->addValidator('Size', false , array(
                         'max' => '10MB'
                         ))
                    // disable - move file to destionation when calling method getValues
                     ->setValueDisabled(true)
                     ->setRequired(false); 
        
        $this->addElement($clientPhoto);
    }
   
}
