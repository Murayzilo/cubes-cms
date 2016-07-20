<?php
class Application_Model_DbTable_CmsUsers extends Zend_Db_Table_Abstract
    {
    const DEFAULT_PASSWORD = 'cubesphp';
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_users';//podesavanje imena tabele
    
    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_users table columns or NULL if not found
     */
    public function getUserById($id){
        
        
        $select = $this->select();
        $select->where('id = ?', $id);
        
        $row =  $this->fetchRow($select);//find vraca niz objekata tj vise redova, ne samo jedan
        
        if($row instanceof Zend_Db_Table_Row){
            
            return $row->toArray();
        }else {
            //row is not found
            return NULL;
        }
    }

    
    /**
     * 
     * @param type $user
     *  @param array $user Associative array with keys at column names and values as column new values
     * @return int ID of new user
     */
    public function insertUser($user) {
        
        //set default password for new user
        $user['password'] = md5(self::DEFAULT_PASSWORD);
        
        return  $this->insert($user);

    }
    
        /**
     * @param type $id
     * @param array $user Associative array with keys at column names and values as column new values
     */
    public function updateUser($id, $user){
        if(isset($user['id'])){
            //forbid changing of user id (izbegavamo da se promeni id usera, brise se iz niza ukoliko je setovan)
            unset($user['id']);
        }
        
        
        $this->update($user, 'id = ' . $id);
        
        
    }
    /**
     * @param type int $id
     * @param string $newPassword Plain password, noy hashed
     */
    public function changeUserPassword($id, $newPassword)
    {
        //update "password" column, set md5 value of new password, for user with id = $id
        $this->update(array('password'=> md5($newPassword)), 'id = ' . $id);
    }
    
    }