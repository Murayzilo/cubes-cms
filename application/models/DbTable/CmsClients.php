<?php
class Application_Model_DbTable_CmsClients extends Zend_Db_Table_Abstract {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_name = 'cms_clients';
    /**
     * @param int $id
     * return null|array Associative array with keys as cms_clients table columns or NULL if not found
     */
    public function getClientById($id) {
        $select = $this->select();
        $select->where('id = ?', $id);
        $row = $this->fetchRow($select); 
        if ($row instanceof Zend_Db_Table_Row) {
            return $row->toArray();
        } else {
            //row is not found
            return NULL;
        }
    }
    /**
     * @param type  int $id
     * @param array $client Associative array with keys at column names and values as column new values
     */
    public function updateClient($id, $client) {
        if (isset($client['id'])) {
            //forbid changing of client id
            unset($client['id']);
        }
        $this->update($client, 'id = ' . $id);
    }
    /**
     * 
     * @param array $client Associative array with keys at column names and values as column new values
     * @return int The ID of new client (autoincrement)
     */
    public function insertClient($client) {
       //fetch order number of new client
        

          //trazimo query builder
          $select = $this->select();
          //Sort rows by order_number DESCENDING and fetch one row from the top
          $select->order('order_number DESC');
            //      ->limit(1); ILI na drugi nacin
           $this->fetchRow($select);
           
           $clientWithBiggestOrderNumber = $this->fetchRow($select);
          
           if ($clientWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {        // implementira interface array accessable, mozemo da pristupamo kao u nizu   
               $client['order_number'] = $clientWithBiggestOrderNumber['order_number'] + 1;
        } else {
          //table was empty, we are inserting first client
          $client['order_number'] = 1;
        }
        
        $id = $this->insert($client);
        
        return $id;
    }
    /**
     * 
     * @param int $id ID of client to delete
     */
   public function deleteClient($id) {
       
        $clientPhotoFilePath = PUBLIC_PATH . '/uploads/clients/' . $id . '.jpg';
       if (is_file($clientPhotoFilePath)) {

           //delete client photo file
           unlink($clientPhotoFilePath);
       }
       
        //client who is going to be deleted
        $client = $this->getClientById($id);
        
        $this->update(array(
            //ovako se u zendu naglasava
            'order_number' => new Zend_Db_Expr('order_number - 1')
        ),
            'order_number > ' . $client['order_number']);
        
        $this->delete('id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of client to disable
     */
      public function disableClient($id) {
        
        
       $this->update(array(
           'status' => self::STATUS_DISABLED
       ), 'id= ' . $id);

    }
    /**
     * 
     * @param int $id ID of service to enable
     */
      public function enableClient($id) {
        
        
       $this->update(array(
           'status' => self::STATUS_ENABLED
       ), 'id=' . $id);

    }
    
     /**
     * 
     * @param int $sortedIds ID of client to sort
     */
        public function updateOrderOfClients($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            $this->update(array(
                'order_number' => $orderNumber + 1 // +1 because it starts from 0
                    ), 'id = ' . $id);
        }
    }

    public function countAllClients(){
        
        $select = $this->select();
        //reset previously set columns for resultset
        $select->reset('columns');
        //SET one column/field to fetch and it is COUNT  function
        $select->from($this->_name,'COUNT(*) AS total');
        $row = $this->fetchRow($select);
        //die($select->assemble());
        return $row['total'];
    }
    
    public function countActiveClients(){
        
        $select = $this->select();
        //reset previously set columns for resultset
        $select->reset('columns');
        //SET one column/field to fetch and it is COUNT  function
        $select->from($this->_name,'COUNT(*) AS active')
               ->where('status = ' . self::STATUS_ENABLED);
        $row = $this->fetchRow($select);
        //die($select->assemble());
        return $row['active'];
    }
}
