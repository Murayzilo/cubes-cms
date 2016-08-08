<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function askmemberAction(){
        $request = $this->getRequest();
        
        $id = $request->getParam('id');
        $id = trim($id);
        $id = (int)$id;

        if (empty($id)){   
            throw  new Zend_Controller_Router_Exception('No member id', 404);
        
        }

        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();

        $select = $cmsMembersDbTable->select();
        $select->where('id =?',  $id)
                ->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED);

        
       $member = $cmsMembersDbTable->fetchRow($select)->toArray();
        
        $this->view->member = $member;
    }
}

