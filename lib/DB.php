<?php


class DB
{
    private static $db_hostname = "";
    private static $db_username = "";
    private static $db_password = "";
    private static $db_database = "";

    private static $db = null;

    public static $hataMesaji = "";

    private static $currSorgu = "SELECT * FROM :table";
    private static $currTable = "";
    private static $currParameter = array();
    private static $sonSorgu = "";
    private static $sonTablo = "";
    private static $sonParametreler = array();

    public function __construct($db_hostname = "", $db_username = "", $db_password = "", $db_database = "")
    {
        if(!$db_hostname)
        {
            return;
        }
        $this->db_hostname = $db_hostname;
        $this->db_username = $db_username;
        $this->db_password = $db_password;
        $this->db_database = $db_database;
        try 
        {
            $this->db = new PDO("mysql:host=".$this->db_hostname.";dbname=".$this->db_database.";charset=utf8", $this->db_username, $this->db_password);
        } 
        catch ( PDOException $e )
        {
            print $e->getMessage();
        }
    }

    public static function query($query = "", $parametreler = null)
    {
        if(!self::$db)
        {
            $_database = require_once("config/database.php");

            self::$db_hostname = $_database[$_database["type"]]["hostname"];
            self::$db_username = $_database[$_database["type"]]["username"];
            self::$db_password = $_database[$_database["type"]]["password"];
            self::$db_database = $_database[$_database["type"]]["database"];

            try 
            {
                self::$db = new PDO("mysql:host=".self::$db_hostname.";dbname=".self::$db_database.";charset=utf8", self::$db_username, self::$db_password);
            } 
            catch ( PDOException $e )
            {
                print $e->getMessage();
            }
        }

        if(!$query)
        {
            return null;
        }
        $degerler = array();

        $sql = self::$db->prepare($query);
        if($parametreler)
        {
        	//echo $query;
        	//print_r($parametreler);
            $sql->execute($parametreler);
        }
        else
        {
            $sql->execute();
        }
        $i = 0;
        while($satir = $sql->fetch(PDO::FETCH_ASSOC))
        {
            foreach($satir as $key => $value)
            {
                $degerler[$i][$key] = $value;
            }
            $i++;
        }

        self::$sonSorgu = $sql->queryString;
        self::$sonTablo = self::$currTable;
        self::$sonParametreler = $parametreler;
        self::$hataMesaji = $sql->errorInfo();
        
        //require_once("DatabaseResult.php");
        return new DatabaseResult($degerler);
    }

    public static function table($table)
    {
        self::clear();
        self::$currSorgu = str_replace(":table", "`$table`", self::$currSorgu);
        self::$currTable = $table;
        return new self;
    }
    public static function select($parametreler)
    {
        $select = "";
        $i = 0;
        if(is_array($parametreler))
        {
            foreach ($parametreler as $key => $value) 
            {
                if($i++ > 0) $select .= " , ";
                $select .= " `$value` ";
            }
        }
        else
        {
            $select = " `$parametreler` ";
        }
        self::$currSorgu = str_replace(" * ", $select, self::$currSorgu);
        return new self;
    }
    public static function insert($parametreler, $parametre2 = "")
    {
        $keys = "";
        $values = "";
        $i = 0;
        if(is_array($parametreler))
        {
            foreach ($parametreler as $key => $value) 
            {
                if($i++ > 0)
                {
                    $keys .= " , ";
                    $values .= " , ";
                }
                $a = 1;
                while(strstr(self::$currSorgu,":".$key.$a."insert")) $a++;
                $keys .= " `$key` ";
                $values .= " :".$key.$a."insert ";
                self::$currParameter[":".$key.$a."insert"] = $value;
            }
        }
        else
        {
            $keys = " `$parametreler` ";
            $a = 1;
            while(strstr(self::$currSorgu,":".$parametreler.$a."insert")) $a++;
            $values = " :".$parametreler.$a."insert ";
            self::$currParameter[":".$parametreler.$a."insert"] = $parametre2;
        }
        $tablo = (self::$currTable ? self::$currTable : self::$sonTablo);
        self::$currSorgu = "INSERT INTO `".$tablo."` ( $keys ) VALUES( $values );";
        self::exec();
        return new self;
    }
    public static function update($parametreler, $parametre2 = "")
    {
        if(is_array($parametreler))
        {
	        self::$currSorgu = "UPDATE ".self::$currTable." SET ";
	        $i = 0;
	        foreach ($parametreler as $key => $value) 
	        {
	            if($i++ > 0)
	                self::$currSorgu .= ",";
	            self::$currSorgu .= " `$key`=:".$key."update ";
	            self::$currParameter[$key."update"] = $value;
	        }
	    }
	    else{
			self::$currSorgu = "UPDATE ".self::$currTable." SET ".$parametreler."=:".$parametreler."update ";
	            self::$currParameter[$parametreler."update"] = $parametre2;
		}

        return new self;
    }
    public static function delete($parametreler = "", $parametre2 = "")
    {
        $tablo = (self::$currTable ? self::$currTable : self::$sonTablo);
        self::$currSorgu = "DELETE FROM `$tablo` ";
        if($parametreler)
        {
            if(is_array($parametreler))
            {
                $i = 0;
                foreach ($parametreler as $key => $value) 
                {
                    if($i++ > 0)
                        self::$currSorgu .= " AND ";
                    else
                        self::$currSorgu .= " WHERE ";
                    $a = 1;
                    while(strstr(self::$currSorgu,":".$key.$a."insert")) $a++;
                    self::$currSorgu .= " `$key`=:".$key.$a."where ";
                    self::$currParameter[":".$key.$a."where"] = $value;
                }
            }
            else
            {
                self::$currSorgu .= " WHERE ";
                $a = 1;
                while(strstr(self::$currSorgu,":".$parametreler.$a."insert")) $a++;
                self::$currSorgu .= " `$parametreler`=:".$parametreler.$a."where ";
                self::$currParameter[":".$parametreler.$a."where"] = $parametre2;
            }

            self::exec();
        }

        return new self;
    }
    public static function where($parametreler, $parametre2 = "")
    {
        $i = 0;
        if(!strstr(self::$currSorgu," WHERE "))
        {
            self::$currSorgu .= " WHERE ";
        }
        else
        {
            $i = 1;
        }
        if(is_array($parametreler))
        {
            foreach ($parametreler as $key => $value) 
            {
                $key = str_replace(":", "", $key);
                if($i++ > 0)
                    self::$currSorgu .= " AND ";
                $a = 1;
                while(strstr(self::$currSorgu,":".$key.$a."where")) $a++;
                self::$currSorgu .= " `$key`=:".$key.$a."where ";
                self::$currParameter[":".$key.$a."where"] = $value;
            }
        }
        else
        {
            $parametreler = str_replace(":", "", $parametreler);
            if($i > 0)
                self::$currSorgu .= " AND ";
            $a = 1;
            while(strstr(self::$currSorgu,":".$parametreler.$a."where")) $a++;
            self::$currSorgu .= " `$parametreler`=:".$parametreler.$a."where ";
            self::$currParameter[":".$parametreler.$a."where"] = $parametre2;
        }

        return new self;
    }
    public static function andWhere($parametreler, $parametre2 = "")
    {
        $i = 0;
        if(!strstr(self::$currSorgu," WHERE "))
        {
            self::$currSorgu .= " WHERE ";
        }
        else
        {
            $i = 1;
        }
        if(is_array($parametreler))
        {
            foreach ($parametreler as $key => $value) 
            {
                $key = str_replace(":", "", $key);
                if($i++ > 0)
                    self::$currSorgu .= " AND ";
                $a = 1;
                while(strstr(self::$currSorgu,":".$key.$a."where")) $a++;
                self::$currSorgu .= " `$key`=:".$key.$a."where ";
                self::$currParameter[":".$key.$a."where"] = $value;
            }
        }
        else
        {
            $parametreler = str_replace(":", "", $parametreler);
            if($i > 0)
                self::$currSorgu .= " AND ";
            $a = 1;
            while(strstr(self::$currSorgu,":".$parametreler.$a."where")) $a++;
            self::$currSorgu .= " `$parametreler`=:".$parametreler.$a."where ";
            self::$currParameter[":".$parametreler.$a."where"] = $parametre2;
        }

        return new self;
    }
    public static function orWhere($parametreler, $parametre2 = "")
    {
        $i = 0;
        if(!strstr(self::$currSorgu," WHERE "))
        {
            self::$currSorgu .= " WHERE ";
        }
        else
        {
            $i = 1;
        }
        if(is_array($parametreler))
        {
            foreach ($parametreler as $key => $value) 
            {
                $key = str_replace(":", "", $key);
                if($i++ > 0)
                    self::$currSorgu .= " OR ";
                $a = 1;
                while(strstr(self::$currSorgu,":".$key.$a."where")) $a++;
                self::$currSorgu .= " `$key`=:".$key.$a."where ";
                self::$currParameter[":".$key.$a."where"] = $value;
            }
        }
        else
        {
            $parametreler = str_replace(":", "", $parametreler);
            if($i > 0)
                self::$currSorgu .= " OR ";
            $a = 1;
            while(strstr(self::$currSorgu,":".$parametreler.$a."where")) $a++;
            self::$currSorgu .= " `$parametreler`=:".$parametreler.$a."where ";
            self::$currParameter[":".$parametreler.$a."where"] = $parametre2;
        }

        return new self;
    }
    public static function orderBy($parametreler, $siralama = "")
    {
        $i = 0;
        $sorguEk = "";
        if($sorguEk = strstr(self::$currSorgu," ORDER BY "))
        {
            self::$currSorgu = str_replace($sorguEk, "", self::$currSorgu);
            $sorguEk = str_replace(" ORDER BY ", "", $sorguEk);
        }
        self::$currSorgu .= " ORDER BY ";
        if(is_array($parametreler))
        {
            foreach ($parametreler as $key => $value) 
            {
                if($i++ > 0)
                    self::$currSorgu .= " , ";
                if(is_int($key))
                {
                    self::$currSorgu .= " `$value` ".strtoupper($siralama)." ";
                }
                else
                {
                    self::$currSorgu .= " `$key` ".strtoupper($value)." ";
                }
            }
        }
        else
        {
            self::$currSorgu .= " `$parametreler` ".strtoupper($siralama)." ";
        }
        if($sorguEk)
        {
            self::$currSorgu .= ",".$sorguEk;
        }

        return new self;
    }
    public static function limit($parametre)
    {
        if(!is_int($parametre))
        {
            return new self;
        }
        self::$currSorgu .= " LIMIT $parametre ";

        return new self;
    }
    public static function exec()
    {
        $sonuc = self::query(self::$currSorgu, self::$currParameter);

        self::clear();
        
        return ($sonuc);
    }
    public static function execute()
    {
        return self::exec();
    }
    public static function getAll()
    {
        return self::exec();
    }
    public static function getLastQuery()
    {
        $sorgu = self::$sonSorgu;
        foreach (self::$sonParametreler as $key => $value) 
        {
            if(is_int($value))
                $sorgu = str_replace($key, $value, $sorgu);
            else
                $sorgu = str_replace($key, "'$value'", $sorgu);
        }
        return $sorgu; 
    }
    
    private static function clear()
    {
        self::$currTable = "";
        self::$currSorgu = "SELECT * FROM :table";
        self::$currParameter = array();
    }
}


class DatabaseResult
{
    private $degerler = null;
    private $currDeger = array();
    private $currIndex = 0;

    public function __construct($degerler)
    {
        $this->degerler = $degerler;
    }

    public function isNull()
    {
        return ($this->degerler ? false : true);
    }

    public function first()
    {
        if(!array_key_exists(0, $this->degerler))
        {
            return false;
        }

        $this->currIndex = 0;
        foreach ($this->degerler[$this->currIndex] as $key => $value) 
        {
            $this->currDeger[$key] = $value;
        }
        return true;
    }

    public function next()
    {
        if(!array_key_exists($this->currIndex, $this->degerler))
        {
            return false;
        }
        foreach ($this->degerler[$this->currIndex] as $key => $value) 
        {
            $this->currDeger[$key] = $value;
        }
        $this->currIndex++;
        return true;
    }

    public function length()
    {
        return count($this->degerler);
    }

    public function __get($varName)
    {
        if (!array_key_exists($varName,$this->currDeger))
        {
            //this attribute is not defined!
            throw new Exception('Column is not defined: '.$varName);
        }
        else return $this->currDeger[$varName];
    }
}