<?
	require('/home/a1466265/public_html/Oauth2/Client.php'); 
	require('/home/a1466265/public_html/Oauth2/GrantType/IGrantType.php'); 
	require('/home/a1466265/public_html/Oauth2/GrantType/AuthorizationCode.php'); 
	
	/*
		Constants
	*/
	class Constants
	{
		public static $mysite;
		public static $uploadImages;
		
		public function InitConstants()
		{
			Constants::$mysite = '/home/a1466265/public_html/';
			Constants::$uploadImages = Constants::$mysite . 'upload/images/';
		}
	}
	
	Constants::InitConstants();
	
	Logger::SetLogLevel(Logger::debug);//Logger::nationBuilder, Logger::debug
	$handler = new BaseObjectHandler();
	$handler->handleRequest(); 
	
	
	//Objects and Functions below here
	//********************************
	
	/*
		Handler for doing whatever is needed for every php request
	*/
	class BaseObjectHandler
	{
		private $con;
		
		public function __construct()
		{
			// Create connection
			$this->con = new MySqlDB($this);
		}

		/*
			Determines how to handle a request
		*/
		public function handleRequest()
		{
			try
			{
				Logger::LogRequest('incoming.log', Logger::debug);
			
				$resource = $_REQUEST['resource'];
				$action = $_REQUEST['action'];
				
				/*
					Potential Security Implementation: Add login request as first request with userid and creation date; verify the data 
					Then Add security checks based on the userid that is logged in.
				*/
				
				if($resource == "" && $action == "")
				{	
					$nb = new NationBuilder();
					$code = $_REQUEST['code'];
					if($code != "")
						$nb->ClientSetup($code);
					if($_REQUEST['sync'] == 1)
						$nb->SynchronizeUsers($this->con);
					
					//Check if this is a nationbuilder webhook:
					{
						$json = file_get_contents('php://input');
						$data = json_decode($json, true);
						if($data['nation_slug'] == 'thetransition')
						{
							$person = $data['payload']['person'];
							$user = new users($this->con);
							$user->createupdate($person);
						}
					}
				}
				else
				{
					Security::VerifySecurity($this->con);
					
					$object = new $resource($this->con);
					$results = $object->ProcessAction($action);
			
					$this->outputSuccess($results);
				}
			}
			catch(Exception $e)
			{
				Logger::LogError($e->getMessage(), Logger::fatalError);
				$this->outputFailure($e->getMessage());
			}
		}
		
		/*
			Successful json data output
			@data - an array of data to output
		*/
		private function outputSuccess($data)
		{
			$data['success'] = true;
			$this->outputData($data);
		}
		
		/*
			Failed json data output
			@message - the error message to output
		*/
		private function outputFailure($message)
		{
			$results = array();
			$results['success'] = false;
			$results['errortxt'] = $message;
			$this->outputData($results);
		}
		
		/*
			Outputs json data back to the requester
			@data - an array of data to output
		*/
		private function outputData($data)
		{
			$json = json_encode($data);

			$callback = $_REQUEST['callback'];
			//start output
			if ($callback) {
				header('Content-Type: text/javascript');
				echo $callback . '(' . $json . ');';
			} else {
				header('Content-Type: application/x-json');
				echo $json;
			}
		}
	}
	
	class Security
	{
		public static $userid;
		public static $creationdt;
		
		public static function VerifySecurity($database)
		{
			self::$userid = $_REQUEST['id1'];
			self::$creationdt = $_REQUEST['id2'];
			
			$statement = "Select count(*) from users where id = (?)";// and creationdt = (?)";
			$parameters[] = self::$userid;
			//$parameters[] = self::$creationdt;
			
			$count = $database->rowCount($statement, $parameters);
			if($count == 1)
				return true;
			throw new Exception("User is not allowed access to this system");
		}
		public static function ValidateColumn($columnName, $columnValue)
		{
			if($columnName == 'userid')
			{
				if(self::$userid == $columnValue)
					return true;
				throw new Exception("Attempt to set a record to an invalid user");
			}
			return true;
		}
	}
	
	/*
		Abstract Object for defining required functionality
	*/
	abstract class AbstractObject
	{
		/*
			Process the given action by calling the function associated with the action
			@action - the action to call
		*/
		public function ProcessAction($action)
		{
			if(is_callable(array($this, $action)))
			{
				$this->SetInput();
				$this->$action();
				$this->ProcessImageFile($action);
				return $this->GetOutput();
			}
			else
			{
				throw new Exception("The " . $action . " action is not available for object: " . get_class($this)); 
			}
		}
		
		/*
			Subclasses should implement this to set all of the input needed
		*/
		abstract protected function SetInput();
		
		/*
			Subclasses should implement this to retrieve the output that should be sent back to the browser
		*/
		abstract protected function GetOutput();
		
		/*
			Subclasses should implement this to process ImageFile items which are base64 encoded
		*/
		abstract protected function ProcessImageFile($action);
		
		/*
			Create a new object
		*/
		protected function create()
		{
			throw new Exception("Create method not implemented for " . get_class($this));
		}
		/*
			Update the object
		*/
		protected function update()
		{
			throw new Exception("Update method not implemented for " . get_class($this));
		}
		/*
			Delete the object
		*/
		protected function delete()
		{
			throw new Exception("Delete method not implemented for " . get_class($this));
		}
		/*
			Retrieves data about the object
		*/
		protected function read()
		{
			throw new Exception("Read method not implemented for " . get_class($this));
		}
	}
	
	/*
		Base object used for any generic functionality that is needed for all data tables
	*/
	class BaseObject extends AbstractObject
	{
		protected $connection;
		protected $data;
		protected $properties;
		
		public function __construct($con)
		{
			$this->connection = $con;
			$this->properties = array();
		}
		
		protected function SetInput()
		{
			$this->SetData($this->GetRequestData());
		}
		
		protected function GetOutput()
		{
			$output = $this->ConvertInputRecordsToOutput($this->data);
			foreach($this->properties as $key => $value)
			{
				$output[$key] = $value;
			}
			return $output;
		}
		
		/*
			This function assumes that the records have id and imageFile attributes
		*/
		protected function ProcessImageFile($action)
		{
			if($action == 'create' || $action == 'update')
			{
				foreach($this->GetData() as $record)
				{
					$imageFile = $record->imageFile;
					if($imageFile == "")
						continue;
					$id = $record->id;
					if($id == "")
						continue;
					$encodedData = str_replace(' ','+',$imageFile);
					$decodedData = base64_decode($encodedData);
					
					$uriPhp = 'data://' . substr($imageFile, 5);
					$binary = file_get_contents($uriPhp);
					
					$fileName = Constants::$uploadImages . get_class($this) . "/" . $id . ".png";//Is it safe to just always use .png?
					file_put_contents($fileName, $binary);
				}
			}
			else if($action == 'delete')
			{
				foreach($this->GetData() as $record)
				{
					$id = $record->id;
					if($id == "")
						continue;
					$fileName = Constants::$uploadImages . get_class($this) . "/" . $id . ".png";
					if(file_exists($fileName))
						unlink($fileName);
				}
			}
		}
		
		public function GetData()
		{
			return $this->data;
		}
		
		public function SetData($d)
		{
			$this->data = $d;
		}
		
		public function AddProperty($name, $value)
		{
			$this->properties[$name] = $value;
		}
		
		/*
			Retrieves the data object from the request global variable and json decodes it
			@return - array of elements in the data object
		*/
		private function GetRequestData()
		{
			$json = stripslashes($_REQUEST['data']);
			return json_decode($json);
		}
		
		/*
			Converts the input records from GetRequestData into the proper output format
			@records - The array of input records
			@return - The array of output records in proper format
		*/
		private function ConvertInputRecordsToOutput($records)
		{
			$results = array();
			$results[get_class($this)] = $records;
			return $results;
		}
	}
	
	/*
		Generic Table Object for handling the base functions (create, read, update, delete)
		Read - Should be able to handle sorting and filtering
		Create - Add new record
		Update - Update existing record
		Delete - Delete existing record
	*/
	abstract class TableObject extends BaseObject
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		abstract protected function GetPrimaryTable();
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		abstract protected function GetPrimaryTableView();
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		abstract protected function GetColumns();
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		abstract protected function GetColumnsView();
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		abstract protected function GetDefaultSortColumn();
		
		/*
			Retrieves whether or not the table has an auto-increment id.  These id's need to be handled and returned back to the user as they are generated so that update works properly.
			Note: We assume that if this is true then the column name is 'id'
			@return true if the table has this field otherwise false
		*/
		protected function HasAutoIncrementId()
		{
			return false;
		}
		
		/*
			Verifies the field is valid based on whether or not the column exists as a column in our view
		*/
		protected function ValidateColumnView($columnName)
		{
			$columns = $this->GetColumnsView();
			$columnNames = $columns->GetNames();
			if(in_array($columnName, $columnNames))//See if the column is a valid column for the view
				return true;
			return false;
		}
		
		/*
			Function to allow us to set values for special fields like creation dates.
		*/
		protected function SetValueForCreateUpdate($record, $columnName)
		{
			if($columnName == 'lastupdated')
				return date('c');
			else return $record->$columnName;
		}
		
		
		
		protected function create()
		{
			$columns = $this->GetColumns();
			$arrayInsertFields = array_fill(0, $columns->GetCount(), "?");
			$statement = "INSERT INTO " . $this->GetPrimaryTable() . " (" . implode(",", $columns->GetNames()) . ")" . " VALUES (" . implode(",", $arrayInsertFields) . ")";
			$records = $this->GetData();
			foreach($records as $record)
			{
				$parameters = array();
				foreach($columns->GetNames() as $column)
				{
					Security::ValidateColumn($column, $record->$column);
					$parameters[] = $this->SetValueForCreateUpdate($record, $column);
				}
				$this->connection->execute($statement, $parameters, false);
				if($this->HasAutoIncrementId())
					$record->id = $this->connection->lastInsertId();
			}
		}
		protected function update()
		{
			$columns = $this->GetColumns();
			$statement = "Update " . $this->GetPrimaryTable() . " set ";
			$where = " where ";
			
			$set = "";
			$criteria = "";
			foreach($columns->GetColumns() as $column)
			{
				//If the column is not a key then set the column
				if(!($column->IsKey()))
				{
					if($set != "")
						$set = $set . ", ";
					$set = $set . $column->GetName() . " = (?)";
				}
				else //Otherwise use the key to find the record
				{
					if($criteria != "")
						$criteria = $criteria . " and ";
					$criteria = $criteria . $column->GetName() . " = (?)";
				}
			}
			if($set == "")
				throw new Exception("No values set.  At least one column must be set in an update.");
			if($criteria == "")
				throw new Exception("No keys set.  At least one key is required in an update");
			$statement = $statement . $set . $where . $criteria;
			
			$records = $this->GetData();
			foreach($records as $record)
			{
				$parameters = array();
				foreach($columns->GetColumns() as $column)
				{
					if(!$column->IsKey())//All set columns must come before all key columns
					{
						$columnName = $column->GetName();
						Security::ValidateColumn($columnName, $record->$columnName);
						$parameters[] = $this->SetValueForCreateUpdate($record, $columnName);
					}
				}
				foreach($columns->GetKeys() as $key)
				{
					Security::ValidateColumn($key, $record->$key);
					$parameters[] = $record->$key;
				}
				$this->connection->execute($statement, $parameters, false);
			}
		}
		protected function delete()
		{
			$columns = $this->GetColumns();
			$statement = "Delete from " . $this->GetPrimaryTable() . " where ";
			$where = "";
			foreach($columns->GetKeys() as $key)
			{
				if($where != "")
					$where = $where . " and ";
				$where = $where . $key . " = (?)";
			}
			if($where == "")
				throw new Exception("No keys set.  At least one key is required to delete.");
			$statement = $statement . $where;
			
			$records = $this->GetData();
			foreach($records as $record)
			{
				$parameters = array();
				foreach($columns->GetKeys() as $key)
				{
					Security::ValidateColumn($key, $record->$key);
					$parameters[] = $record->$key;
				}
				$this->connection->execute($statement, $parameters, false);
			}
		}
		
		protected function read()
		{
			//Grab sorting
			$json = stripslashes($_REQUEST['sort']);
			$sort = json_decode($json);
			$orderColumn = $sort[0]->property;
			if(!$this->ValidateColumnView($orderColumn))
				$orderColumn = $this->GetDefaultSortColumn();
			$orderDirection = $sort[0]->direction;
			if($orderDirection == "DESC")
				$orderDirection = "DESC";
			else $orderDirection = "ASC";
			
			$orderby = " Order By " . $orderColumn . " " . $orderDirection;
			
			//Add paging parameters if they exist
			$limit = $_REQUEST['limit'];
			$start = $_REQUEST['start'];
			$strLimit = "";
			if($limit != "" && $start != "")
			{
				$start = intval($start);
				$limit = intval($limit);
				if($start < 0)
					$start = 0;
				if($limit <= 0)
					$limit = 25;
				$strLimit = " LIMIT " . $start . ", " . $limit;
			}
			
			$select = "SELECT * FROM " . $this->GetPrimaryTableView();
			$where = "";
			$parameters = array();
			
			//Use count to find the actual total results since we might be paging
			$countSelect = "SELECT COUNT(*) FROM " . $this->GetPrimaryTableView();
			$countParameters;
			
			//Implement filtering
			$filters = stripslashes($_REQUEST['filter']);
			if($filters != "")
			{
				$filters = json_decode($filters);
				$criteria = "";
				foreach($filters as $filter)
				{
					$property = $filter->property;
					if(!$this->ValidateColumnView($property))
						continue;
					$value = $filter->value;
					$operator = $filter->operator;
					switch($operator)
					{
						case "like":
							$value = "%" . $value . "%";
							break;
						case "eq":
							$operator = "=";
							break;
						case "ne":
							$operator = "!=";
							break;
						case "gt":
							$operator = ">";
							break;
						case "lt":
							$operator = "<";
							break;
						default:
							throw new Exception("Invalid operator given: " . $operator);
					}
					if($property != "" && $value != "" && $operator != "")
					{
						if($criteria != "")
							$criteria = $criteria . "and ";
						$criteria = $criteria . $property . " " . $operator . " (?) ";
						$parameters[] = $value;
						$countParameters[] = $value;
					}
				}
				$where = " where " . $criteria;
			}
			
			$statement = $select . $where . $orderby . $strLimit;
			$countStatement = $countSelect . $where;
	
			//Execute the statements and return the results
			$results = array();
			$this->AddProperty("total", $this->connection->rowCount($countStatement, $countParameters));
			
			//TODO: Possibly clean this up??
			$resultSet = $this->connection->execute($statement, $parameters);
			foreach($resultSet as $row)
			{
				$arrayResult = array();
				foreach($this->GetColumnsView()->GetNames() as $column)
				{
					$arrayResult[$column] = $row[$column];
				}
				$results[get_class($this)][] = $arrayResult;
			}
			$count = count($results[get_class($this)]);
			if($count == 0)
			{
				$results[get_class($this)] = array();
			}
			
			$this->SetData($results[get_class($this)]);
		}
	}
	
	/*
		Handles data interaction for the userskills table
	*/
	class userskills extends TableObject 
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "userskills";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return 'userskillsview';
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('userid', 'skillid', 'userrating'), array('userid', 'skillid'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return new ColumnList($this->GetPrimaryTableView(), array('userid', 'skillid', 'userrating', 'fullname', 'name'), array('userid', 'skillid'));
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
		
		protected function create()
		{
			parent::create();
			$records = $this->GetData();
			$userid = "";
			
			$skillids;
			foreach($records as $record)
			{
				$skillids[] = $record->skillid;
				$userid = $record->userid;
			}
			
			if($userid != "")
			{
				$skills = new skills($this->connection);
				$skillnames = $skills->GetSkillNames($skillids);
				$nb = new NationBuilder();
				$nb->PushTags($userid, $skillnames);
			}
		}
		
		protected function delete()
		{
			parent::delete();
			$records = $this->GetData();
			$userid = "";
			$skillids;
			foreach($records as $record)
			{
				$skillids[] = $record->skillid;
				$userid = $record->userid;
			}
			
			if($userid != "")
			{
				$skills = new skills($this->connection);
				$skillnames = $skills->GetSkillNames($skillids);
				$nb = new NationBuilder();
				foreach($skillnames as $skillname)
				{
					$nb->DeleteTag($userid, $skillname);
				}
			}
		}
	}
	
	/*
		Handles data interaction for the userspaces table
	*/
	class userspaces extends TableObject 
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "userspaces";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return $this->GetPrimaryTable() . 'view';
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'userid', 'spaceid', 'privacy', 'name', 'description', 'location', 'latitude', 'longitude'), array('id'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'userid', 'spaceid', 'privacy', 'name', 'description', 'location', 'latitude', 'longitude', 'fullname', 'icon'), array('id'));
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
	}
	
	/*
		Handles data interaction for the spaces table
	*/
	class spaces extends TableObject 
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "spaces";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return $this->GetPrimaryTable();
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'name', 'icon'), array('id'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return $this->GetColumns();
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
		
		protected function create()
		{
			throw new Exception("Function not allowed");
		}
		protected function update()
		{
			throw new Exception("Function not allowed");
		}
		protected function delete()
		{
			throw new Exception("Function not allowed");
		}
	}
	
	/*
		Handles data interaction for the skills table
	*/
	class skills extends TableObject
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "skills";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return $this->GetPrimaryTable();
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'name'), array('id'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return $this->GetColumns();
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
		
		protected function create()
		{
			throw new Exception("Function not allowed");
		}
		protected function update()
		{
			throw new Exception("Function not allowed");
		}
		protected function delete()
		{
			throw new Exception("Function not allowed");
		}
		
		//Gather skill names based on skill ids.
		public function GetSkillNames($ids)
		{
			if(count($ids) > 0)
			{
				$statement = "SELECT * FROM skills where id in (";
				$values = "";
				$parameters = array();
				foreach($ids as $id)
				{
					$parameters[] = $id;
					if($values != "")
					{
						$values = $values . ", ";
					}
					$values = $values . "?";
				}
				$statement = $statement . $values . ")";
				$skillnames = $this->connection->execute($statement, $parameters);
				$names = array();
				foreach($skillnames as $skillname)
				{
					$names[] = $skillname['name'];
				}
				return $names;
			}
			return array();
		}
	}
	
	class userreviews extends TableObject
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "userreviews";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return "userreviewsview";
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('reviewerid', 'revieweeid', 'name', 'review', 'lastupdated'), array('reviewerid', 'revieweeid'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return new ColumnList($this->GetPrimaryTable(), array('reviewerid', 'revieweeid', 'name', 'review', 'lastupdated', 'reviewerfullname'), array('reviewerid', 'revieweeid'));
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'lastupdated';
		}
	}
	
	/*
		Handles data interaction for the users table
	*/
	class users extends BaseObject
	{
		/*
			This is a nationbuilder webhook for creating/updating users. 
			@person - person object as defined by the returned json object from NationBuilder
		*/
		public function createupdate($person)
		{
			Logger::LogRequest('createupdate.log', Logger::nationBuilder);
			$id = $person['id'];
			$name = $person['first_name'] . " " . $person['last_name'];
			$creationdt = $person['created_at'];
			
			$parameters[] = $id;
			$total = $this->connection->rowCount("SELECT COUNT(*) FROM users where id = (?)", $parameters);
			if($total > 0)//if user exists update
			{
				$param[] = $name;
				$param[] = $creationdt;
				$param[] = $id;
				$this->connection->execute("UPDATE users set fullname = (?), creationdt = (?) where id = (?)", $param, false);
			}
			else//Else create new user
			{
				$parameters[] = $name;
				$parameters[] = $creationdt;
				$this->connection->execute("Insert into users (id, fullname, creationdt) values (?, ?, ?)", $parameters, false);
			}
		}
	}
	
	/*
		Handles data interaction for the object category table
	*/
	class objectcategory extends TableObject
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "objectcategory";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return $this->GetPrimaryTable();
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'name'), array('id'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return $this->GetColumns();
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
		
		protected function create()
		{
			throw new Exception("Function not allowed");
		}
		protected function update()
		{
			throw new Exception("Function not allowed");
		}
		protected function delete()
		{
			throw new Exception("Function not allowed");
		}
	}
	
	/*
		Handles data interaction for the object permanence table
	*/
	class objectpermanence extends TableObject
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "objectpermanence";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return $this->GetPrimaryTable();
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'name'), array('id'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return $this->GetColumns();
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
		
		protected function create()
		{
			throw new Exception("Function not allowed");
		}
		protected function update()
		{
			throw new Exception("Function not allowed");
		}
		protected function delete()
		{
			throw new Exception("Function not allowed");
		}
	}
	
	/*
		Handles data interaction for the user objects table
	*/
	class userobjects extends TableObject
	{
		/*
			Retrieves the primary table for the object
			@return - the primary table as a string
		*/
		protected function GetPrimaryTable()
		{
			return "userobjects";
		}
		
		/*
			Retrieves the view name for the object
			@return - the view of the object
		*/
		protected function GetPrimaryTableView()
		{
			return "userobjectsview";
		}
		
		/*
			Retrieves the column list for the table
			@return - a list of columns for the table
		*/
		protected function GetColumns()
		{
			return new ColumnList($this->GetPrimaryTable(), array('id', 'userid', 'name', 'description', 'image', 'permanenceid', 'categoryid'), array('id'));
		}
		
		/*
			Retrieves the column list for the view
			@return a list of columns for the view
		*/
		protected function GetColumnsView()
		{
			return new ColumnList($this->GetPrimaryTableView(), array('id', 'userid', 'name', 'description', 'image', 'permanenceid', 'categoryid', 'fullname', 'categoryname', 'permanencename'), array('id'));
		}
		
		/*
			Retrieves the default sort column name
			@return the full name of the sort column as a string to use as the default
		*/
		protected function GetDefaultSortColumn()
		{
			return 'name';
		}
		
		/*
			Retrieves whether or not the table has an auto-increment id.  These id's need to be handled and returned back to the user as they are generated so that update works properly.
			Note: We assume that if this is true then the column name is 'id'
			@return true if the table has this field otherwise false
		*/
		protected function HasAutoIncrementId()
		{
			return true;
		}
	}
	
	/*
		Defines a list of columns
	*/
	class ColumnList
	{
		private $columns;
		private $count;
		private $keyCount;
		
		public function __construct($table, $names, $keys)
		{
			$this->count = 0;
			$this->columns = array();
			foreach($names as $name)
			{
				$isKey = in_array($name, $keys);
				$this->columns[] = new Column($table, $name, $isKey);
				$this->count++;
				if($isKey)
					$this->keyCount++;
			}
		}
		/*
			Retrieves the array of columns
		*/
		public function GetColumns()
		{
			return $this->columns;
		}
		/*
			Constructs an array of column names
		*/
		public function GetNames()
		{
			$names = array();
			foreach($this->columns as $column)
			{
				$names[] = $column->GetName();
			}
			return $names;
		}
		/*
			Constructs an array of column full names
		*/
		public function GetFullNames()
		{
			$fullnames = array();
			foreach($this->columns as $column)
			{
				$names[] = $column->GetFullName();
			}
			return $fullnames;
		}
		/*
			Constructs an array of all the key columns with their full names
		*/
		public function GetFullNameKeys()
		{
			$keys = array();
			foreach($this->columns as $column)
			{
				if($column->IsKey())
					$keys[] = $column->GetFullName();
			}
			return $keys;
		}
		/*
			Constructs an array of all the key columns with just the name; no table
		*/
		public function GetKeys()
		{
			$keys = array();
			foreach($this->columns as $column)
			{
				if($column->IsKey())
					$keys[] = $column->GetName();
			}
			return $keys;
		}
		/*
			Retrieves the number of columns in the list
		*/
		public function GetCount()
		{
			return $this->count;
		}
		/*
			Retrieves the number of keys in the list
		*/
		public function GetKeyCount()
		{
			return $this->keyCount;
		}
	}
	
	/*
		Defines a column for a table
	*/
	class Column
	{
		private $table;
		private $name;
		private $isKey;
		const spacer = ".";
		
		public function __construct($table, $name, $isKey)
		{
			$this->table = $table;
			$this->name = $name;
			$this->isKey = $isKey;
		}
		/*
			Retrieves the full name of the field
		*/
		public function GetFullName()
		{
			return $this->table . "_" . $this->name;
		}
		/*
			Retrieves just the name of the field without the table
		*/
		public function GetName()
		{
			return $this->name;
		}
		/*
			Retrieves whether or not the field is a key
		*/
		public function IsKey()
		{
			return $this->isKey;
		}
		/*
			Splits a full name into it's parts and returns an array
		*/
		public static function SplitName($name)
		{
			return explode($this->spacer, $name);
		}
	}
	
	/*
		Handles sending requests to the MySQL Database
	*/
	class MySqlDB
	{
		private $mysql_host = "mysql17.000webhost.com";
		private $mysql_database = "a1466265_nb";
		private $mysql_user = "a1466265_nb";
		private $mysql_password = "spanish2";
		public $connection;
		private $result;
		
		public function __construct()
		{
			$this->connection = new PDO('mysql:host=' . $this->mysql_host . ';dbname=' . $this->mysql_database, $this->mysql_user, $this->mysql_password);
		
			// Check connection
			if ($this->connection->connect_errno) {
			  throw new Exception("Failed to connect to database: " . $this->connection->connect_errno);
			}
			
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		}
		
		public function lastInsertId()
		{
			return $this->connection->lastInsertId();
		}
		
		/* 
			Execute a sql statement and return an array of objects
			@statement - The SQL Statement
			@parameters - An array of parameters for the statment [['type', 'parameter']['type','parameter']...]
			@return - An array of results
		*/
		public function execute($statement, $parameters = null, $fetchData = true)
		{
			if(count($parameters)>0)//prepared statements
			{
				$prepare = $this->connection->prepare($statement);
				$array = $prepare->errorInfo();
				if($array[2]!="")
					throw new Exception("Execute failed: " . $array[2]);
				$prepare->execute($parameters);
				$array = $prepare->errorInfo();
				if($array[2]!="")
					throw new Exception("Execute failed: " . $array[2]);
				if($fetchData)
					return $prepare->fetchAll();
				return array();
			}
			else//normal statment
			{
				$result = $this->connection->query($statement);
				if($fetchData)
					return $result->fetchAll();
				return array();
			}
		}
		
		/*
			Finds the count of the number of records the sql statement would return
			@statement - the SQL statement to execute
			@parameters - an array of parameters to use for prepared queries
		*/
		public function rowCount($statement, $parameters = null)
		{
			if(count($parameters)>0)//prepared statements
			{
				$prepare = $this->connection->prepare($statement);
				$array = $prepare->errorInfo();
				if($array[2]!="")
					throw new Exception("Execute failed: " . $array[2]);
				$prepare->execute($parameters);
				$array = $prepare->errorInfo();
				if($array[2]!="")
					throw new Exception("Execute failed: " . $array[2]);
				return $prepare->fetchColumn();
			}
			else//normal statment
			{
				$result = $this->connection->query($statement);
				return $result->fetchColumn();
			}
		}
	}
	
	/*
		Handles sending information to NationBuilder
	*/
	class NationBuilder
	{
		private $clientId = '35f177ad5138dd4eaa35f68b7db2561779c514e45a32da55369c89412ff1a170'; 
		private $clientSecret = 'dd72c5ac9e2f9d6b181b67782f4fd60dc48fb022449f2678d4eb7ffce806c745'; 
		private $token = '074c27ff03101b65da0f153daece427230298309bab5fca8818982657e6fcae0';
		private $baseApiUrl = 'https://thetransition.nationbuilder.com';
		private $client;
		
		public function __construct()
		{
			$this->client = new Client($this->clientId, $this->clientSecret);
			$this->client->setAccessToken($this->token); 
			//$this->client->setAccessTokenType(Client::ACCESS_TOKEN_BEARER);
		}
		
		/*
			Initiates a request to nationbuilder
			@statement - the api call to make; the base api url is added to the given statement
			@parameters - an array of parameters to pass to the request
			@method - the method type to use (Get, Post, Put, Delete)
			@header - an array of header keys to add to the header
		*/
		public function Execute($statement, $parameters = array(), $method = Client::HTTP_METHOD_GET, $header = array())
		{
			return $this->client->fetch($this->baseApiUrl . $statement, $parameters, $method, $header);
		}
		
		/*
			Synchronizes data from Nationbuilder to our database
			@database - PDO connection to our MySQL database
		*/
		public function SynchronizeUsers($database)
		{
			$user = new users($database);
			$response = $this->Execute('/api/v1/people');
			$results = $response['result']['results'];
			foreach($results as $result)
			{
				$user->createupdate($result);
			}
		}
		
		/*
			Add some person webhooks so that we can keep data in sync
			@database - PDO connection to our MySQL database
		*/
		public function AddWebHooks($database)
		{
			$statement = '/api/v1/webhooks';
			//$this->postDelete($statement . '/54334275dbf3ec30ac008223');
			
			
			$data = array(
				'webhook'=> array(
					'version'=> 4,
					'url'=> 'http://thetransition.comeze.com/api.php?resource=users&action=personcreation',
					'event'=> 'person_creation'
					)
				);
			$response = $this->postRequest($statement, $data);
			print_r($response);
			echo '<br/>';
			
			$data = array(
				'webhook' => array(
					'version'=> 4,
					'url'=> 'http://thetransition.comeze.com/api.php?resource=users&action=personupdate',
					'event'=> 'person_update'
					)
				);
			$response = $this->postRequest($statement, $data);
			
			
			print_r($response);
			echo '<br/>';
			
			print_r($this->Execute($statement));
		}
		
		/*
			Pushes tags to nationbuilder; This function will not delete tags which are missing.
			@id - the id of the user
			@tags - the tags in array to add
		*/
		public function PushTags($id, $tags)
		{
			$statement = '/api/v1/people/' . $id;
			$user = array(
				"person" => array(
					"tags" => $tags
				)
			);
			$this->postRequest($statement, $user, 'PUT');
		}
		
		/*
			Deletes a tag from a user
			@id - The id of the user
			@tag - the tag to remove
		*/
		public function DeleteTag($id, $tag)
		{
			//DELETE /api/v1/people/:id/taggings/:tag
			$statement = '/api/v1/people/' . $id . '/taggings/' . $tag;
			$this->postDelete($statement);
		}
		
		/*
			Views all of the nation's webhooks
		*/
		public function ViewWebHooks()
		{
			$statement = '/api/v1/webhooks';
			print_r($this->Execute($statement));
		}
		
		/*
		The client doesn't seem to work with posting requests so created another function for using curl to post json requests to nationbuilder.
		@$statement - The api endpoint to use
		@$data - the data to send to Nationbuilder (non-json encoded)
		@return - the response from the request already decoded into an array
		*/
		private function postRequest($statement, $data, $type = 'POST')
		{
			$ch = curl_init($this->baseApiUrl . $statement . "?access_token=" . $this->token);
 
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_TIMEOUT, '10'); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, 
			 
			array("Content-Type: application/json","Accept: application/json"));
			 
			$json_data = json_encode($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
			 
			$json_response = curl_exec($ch);
			if ($curl_error = curl_error($ch)) {
				throw new Exception($curl_error, oauthException::CURL_ERROR);
			} 
			curl_close($ch);
			 
			return json_decode($json_response, true);
		}
		
		/*
		Uses curl to post a delete message to Nationbuilder
		@$statement - the endpoint url to use
		*/
		private function postDelete($statement)
		{
			$ch = curl_init($this->baseApiUrl . $statement . "?access_token=" . $this->token);
 
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_TIMEOUT, '10'); 
			 
			$json_response = curl_exec($ch);
			curl_close($ch);
		}
		
		/*
			Used to setup the nationbuilder token information which is only needed during intial setup.  This function will print the token and code information.
			@code - the code provided by nationbuilder
		*/
		function ClientSetup($code)
		{
			/*Setup Clients*/
			
			$clientId = '35f177ad5138dd4eaa35f68b7db2561779c514e45a32da55369c89412ff1a170'; 
			$clientSecret = 'dd72c5ac9e2f9d6b181b67782f4fd60dc48fb022449f2678d4eb7ffce806c745'; 
			$client = new Client($clientId, $clientSecret); 
			
			
			$redirectUrl    = 'http://thetransition.comeze.com/api.php'; 
			$authorizeUrl   = 'https://thetransition.nationbuilder.com/oauth/authorize'; 
			//$authUrl = $client->getAuthenticationUrl($authorizeUrl, $redirectUrl); 
			//echo $authUrl; 
			
			
			//Generate access token
			echo 'Code value given: ' . $code;
			echo '<br/>';
			//$code = '24a015cf8d03da15baea07b0a23a185cdafc08046939c3fca69ca968761b4407'; 
			// generate a token response
			
			$accessTokenUrl = 'https://thetransition.nationbuilder.com/oauth/token'; 
			$params = array('code' => $code, 'redirect_uri' => $redirectUrl); 
			$response = $client->getAccessToken($accessTokenUrl, 'authorization_code', $params); 
			echo '<br/>';
			print_r($response);
			echo '<br/>';
			// set the client token 
			$token = $response['result']['access_token']; 
			$client->setAccessToken($token); 

			//Test NationBuilder API call
			echo '<br/>';
			print_r('Was given the following token: ' . $token);
			echo '<br/>';
			$baseApiUrl = 'https://thetransition.nationbuilder.com'; 
			$client->setAccessToken($token); 
			$response = $client->fetch($baseApiUrl . '/api/v1/people'); 
			print_r($response); 
		}
	}
	
	/*
		Singleton logger class for logging information to files
	*/
	class Logger
	{
		public static $logLevel;
		
		//Log Level constants
		const debug = 1;//Smaller numbers mean that the event is less likely to be logged
		const nationBuilder = 8;
		const info = 10;
		const fatalError = 100;//The larger the number the more likely the event is to be logged
		
		/*
			Sets the log level for all logger objects
			@level - the level to set the log to; preferably this should be one of the constants defined in this class
		*/
		public static function SetLogLevel($level)
		{
			self::$logLevel = $level;
		}
		
		/*
			Checks if the given log level is allowed based on the current log level set
			@level - the level to check
		*/
		private static function CheckLogLevel($level)
		{
			if(self::$logLevel <= $level)
				return true;
			return false;
		}
		
		/*
			Logs error to the error.log file
			@error - The error message to log
			@level - the log level for the message
		*/
		public static function LogError($error, $level)
		{
			if(self::CheckLogLevel($level))
			{
				$req_dump = $date . " Error: " . $error . "\r\n";
				$filename = 'error.log';
				self::LogData($filename, $req_dump);
			}
		}
		
		/*
			Logs request data to the specified file name
			@filename - the name of the file to log request data to
		*/
		public static function LogRequest($filename, $level)
		{
			if(self::CheckLogLevel($level))
			{
				$req_dump = print_r($_REQUEST, TRUE);
			
				try
				{
					$json = file_get_contents('php://input');
					$data = json_decode($json, true);
				}
				catch (Exception $e)
				{
					$req_dump = $req_dump . " Error: " . $e->getMessage();
				}
				
				$req_dump = $req_dump . "  Extra Data: " . print_r($data, true) . "\r\n";
				self::LogData($filename, $req_dump);
			}
		}
		
		/*
			Logs the data to a file
			@filename - the name of the file to log to
			@req_dump - the dumpped request data to log
		*/
		public static function LogData($filename, $req_dump)
		{
			$file = Constants::$mysite . $filename;
			$fp = fopen($file, 'a');
			$filesize = filesize($file);
			if($filesize > 4000000)
				rename(Constants::$mysite . $filename, Constants::$mysite . $filename . date('m-d-Y-h-i-s', time()));
			fwrite($fp, date('m/d/Y h:i:s ', time()) . $req_dump);
			fclose($fp);
		}
	}
?>