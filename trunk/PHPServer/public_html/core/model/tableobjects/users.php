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
        if($secretKey == '' && version_compare(PHP_VERSION, '5.3.0') >= 0)
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
        $pointPersonId = $person['parent_id'];
        if($pointPersonId == '')
        {
            $potentialPointPerson = $this->AssignPointPerson($id, $lat, $lng);
            if($potentialPointPerson != 0)
            {
                $pointPersonId = $potentialPointPerson;
            }
        }
        $isPointPerson = $person['ispointperson'];
		if($isPointPerson=='')
			$isPointPerson = 0;

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
            $param[] = $pointPersonId;
            $param[] = $isPointPerson;
            $param[] = $id;
            $this->GetConnection()->execute("UPDATE users set fullname = (?), creationdt = (?), profileimage = (?), email = (?), mobile = (?), latitude = (?), longitude = (?), tags = (?), secretkey = (?), pointpersonid = (?), ispointperson = (?) where id = (?)", $param, false);
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
            $parameters[] = $pointPersonId;
            $parameters[] = $isPointPerson;
            $this->GetConnection()->execute("Insert into users (id, fullname, creationdt, profileimage, email, mobile, latitude, longitude, tags, secretkey, pointpersonid, ispointperson) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $parameters, false);
        }
    }
    
    /*
     * Finds all of the available point people and determines who to assign this user to based on proximity
     * @param id - The id of the user to modify
     * @param lat - The latitude of the user
     * @param long - The longitude of the user
     */
    private function AssignPointPerson($id, $lat, $long)
    {
        $pointPerson = 0;
        if($lat != '' && $long != '')
        {
            $query = 'Select id, latitude, longitude from users where ispointperson = 1 and latitude is not null and longitude is not null';
            $results = $this->GetConnection()->execute($query);
            $minDistance = 999999;
            foreach($results as $result)
            {
                $distance = $this->distance($lat, $long, $result['latitude'], $result['longitude'], 'M');
                if($distance < $minDistance)
                {
                    $minDistance = $distance;
                    $pointPerson = $result['id'];
                }
            }
            if($pointPerson != 0)
            {
                $nb = new NationBuilder();
                $nb->PushPointPerson($id, $pointPerson);
            }
        }
        return $pointPerson;
    }
    
    private function distance($lat1, $lon1, $lat2, $lon2, $unit) {
      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);

      if ($unit == "K") {
        return ($miles * 1.609344);
      } else if ($unit == "N") {
          return ($miles * 0.8684);
        } else {
            return $miles;
          }
    }
}
?>
