<?php
/*
 * Handles data interaction for the users table
 * @author jnorcross
 */

class users extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'users';
    }
    
     /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        if (Security::IsAdmin()) {
            return 'users';//Allow admin to see if everything
        }
        return 'usersview';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'fullname';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read');
    }
    
    /*
      This is a nationbuilder webhook for creating/updating users.
      @person - person object as defined by the returned json object from NationBuilder
     */

    public function createupdate($person) {
        Logger::LogRequest('createupdate.log', Logger::nationBuilder);
        $id = $person['id'];
        $name = $person['first_name'] . " " . $person['last_name'];
        $profileImage = $person['profile_image_url_ssl'];
        $creationdt = $person['created_at'];
        $email = $person['email'];//Note there are multiple emails sent from NB; might need additional logic here
        $mobile = $person['mobile'];
        $primaryAddress = $person['primary_address'];
        $lat = $primaryAddress['lat'];
        $lng = $primaryAddress['lng'];
        $tags = $person['tags'];
        $combinedTags = '';
        foreach($tags as $tag)
        {
            if ($combinedTags != '') {
                $combinedTags = $combinedTags . ', ';
            }
            $combinedTags = $combinedTags . $tag;
        }
        $secretKey = $person['secretkey'];
        if($secretKey == '')
        {
            $cstrong = false;
            $bytes = openssl_random_pseudo_bytes(32, $cstrong);
            $hex   = bin2hex($bytes);
            if(!$cstrong)
            {
                throw new Exception("Key could not be made cryptographically secure.");
            }
            $nb = new NationBuilder();
            $nb->PushSecretKey($id, $hex);
            $secretKey = $hex;
        }

        $parameters[] = $id;
        $total = $this->GetConnection()->rowCount("SELECT COUNT(*) FROM users where id = (?)", $parameters);
        if ($total > 0) {//if user exists update
            $param[] = $name;
            $param[] = $creationdt;
            $param[] = $profileImage;
            $param[] = $email;
            $param[] = $mobile;
            $param[] = $lat;
            $param[] = $lng;
            $param[] = $combinedTags;
            $param[] = $secretKey;
            $param[] = $id;
            $this->GetConnection()->execute("UPDATE users set fullname = (?), creationdt = (?), profileimage = (?), email = (?), mobile = (?), latitude = (?), longitude = (?), tags = (?), secretkey = (?) where id = (?)", $param, false);
        } 
        else {//Else create new user
            $parameters[] = $name;
            $parameters[] = $creationdt;
            $parameters[] = $profileImage;
            $parameters[] = $email;
            $parameters[] = $mobile;
            $parameters[] = $lat;
            $parameters[] = $lng;
            $parameters[] = $combinedTags;
            $parameters[] = $secretKey;
            $this->GetConnection()->execute("Insert into users (id, fullname, creationdt, profileimage, email, mobile, latitude, longitude, tags, secretkey) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $parameters, false);
        }
    }
}
?>
