<?php


class Util {

	protected static $privateIpList = array("/^0\./", "/^127\.0\.0\.1/", "/^192\.168\..*/", "/^172\.16\..*/", "/^10..*/", "/^224..*/", "/^240..*/");
	
	//private static $categories = array ("Automotive", "Clothing" ,"Electronics", "Health and Personal Care", "Home and Garden", "Home Improvement", "Pet Supplies", "Shoes and Accessories", "Sports and Outdoors", "Toys and Games", "Watches");
        
        //private static $categories = array ( "Clothing" ,"Electronics", "Health and Personal Care", "Home and Garden",  "Shoes and Accessories", "Sports and Outdoors", "Toys and Games");
        private static $categories = array ( "Clothing" , "Health and Personal Care", "Home and Garden",   "Toys and Games");

        private static $subcategories = array();



	public static final function getDB() {
		return DB::getConnection(DB::MAIN_DB, Config::devMode());
	}
	/**
	 * Returns the user agent of the client.
	 * 
	 * @return	string
	 */
	public static function getUserAgent() {
		if (isset($_SERVER['HTTP_USER_AGENT'])) return $_SERVER['HTTP_USER_AGENT'];
		return '';
	}

	public static function isOnIPhone() {
		$iPhoneUserAgents = array('Amasing/1.0 CFNetwork/459 Darwin/10.0.0d3');
		return (preg_match("/iP(hone|od)/i", self::getUserAgent()) || in_array(self::getUserAgent(), $iPhoneUserAgents)); //user has an iphone/ipod
	}
	
	/**
	 * Returns the current domain.
	 * 
	 * @return	string
	 */
	public static function getCurrentDomain() {
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	}
	
	/**
	 * Simple convenience function that returns whether we're on dev local machine.
	 */ 
	public static function onLocalhost() {
		 if(!isset($_SERVER['HTTP_HOST']))
			 return false;
		return 
			(substr($_SERVER['HTTP_HOST'], 0, 4) == '127.'
			 || substr($_SERVER['HTTP_HOST'], 0, 4) == 'loca'
			 || substr($_SERVER['HTTP_HOST'], -4) == '.dev');
	}
	
	/**
	 * Returns the ip address of the client.
	 *
	 * @return 	string
	 */
	public static function getIpAddress() {
		$REMOTE_ADDR = '';
		if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else $HTTP_X_FORWARDED_FOR = '';
	
		if (!empty($HTTP_X_FORWARDED_FOR)) {
			$match = array();
			if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $HTTP_X_FORWARDED_FOR, $match)) {
				$REMOTE_ADDR = preg_replace(self::$privateIpList, $REMOTE_ADDR, $match[1]);	
			}
		}
	
		return $REMOTE_ADDR;
	}
	
	/**
	 * Returns the request uri of the active request.
	 * 
	 * @return	string
	 */
	public static function getRequestURI() {
		$REQUEST_URI = '';
		/*if (!empty($_SERVER['REQUEST_URI'])) {
			$REQUEST_URI = $_SERVER['REQUEST_URI'];
		}
		else {*/
			if (!empty($_SERVER['ORIG_PATH_INFO']) && strpos($_SERVER['ORIG_PATH_INFO'], '.php') !== false) {
				$REQUEST_URI = $_SERVER['ORIG_PATH_INFO'];
			}
			else if (!empty($_SERVER['ORIG_SCRIPT_NAME'])) {
				$REQUEST_URI = $_SERVER['ORIG_SCRIPT_NAME'];
			}
			else if (!empty($_SERVER['SCRIPT_NAME'])) {
				$REQUEST_URI = $_SERVER['SCRIPT_NAME'];
			}
			else if (!empty($_SERVER['PHP_SELF'])) {
				$REQUEST_URI = $_SERVER['PHP_SELF'];
			}
			else if (!empty($_SERVER['PATH_INFO'])) {
				$REQUEST_URI = $_SERVER['PATH_INFO'];
			}
			if (!empty($_SERVER['QUERY_STRING'])) {
				$REQUEST_URI .= '?'.$_SERVER['QUERY_STRING'];
			}
		//}
		
		//if (!strstr($REQUEST_URI, '.')) $REQUEST_URI = 'index.php';
		
		$REQUEST_URI = str_replace('\\\\', '/', $REQUEST_URI);
		$REQUEST_URI = str_replace('\\', '/', $REQUEST_URI);
		return $REQUEST_URI;
	}

	private static function cleanStr($str)
	{
		return str_replace('&#39;',"'",htmlspecialchars_decode($str));
	}
	
	/**
	 * Returns the request method of the client.
	 * 
	 * @return	string
	 */
	public static function getRequestMethod() {
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
	}
	
	public static function getAllCategories()
	{
	    return Util::$categories;
	}
        public static function getAllSubCategories($category)
        {
            Util::$subcategories ["Automotive"] = array ("Car Care", "Car Electronics & Accessories","Exterior Accessories","Interior Accessories","Lights & Lighting Accessories","Motorcycle & Powersports","Oils & Fluids", "Paint, Body & Trim", "Performance Parts & Accessories", "Replacement Parts", "RV Parts & Accessories","Tires & Wheels","Tools & Equipment","Automotive Enthusiast Merchandise");
            //Util::$subcategories ["Clothing"] = array ("Women","Men","Girls","Boys","Baby","Accessories","Novelty & Special Use","Luggage & Bags","Handbags");
            Util::$subcategories ["Clothing"] = array ("Baby");
            
            
            Util::$subcategories ["Electronics"] = array ("Accessories & Supplies","Camera & Photo","Cell Phones & Accessories","Computers & Accessories","eBook Readers & Accessories","GPS & Navigation","Home Audio","Office Electronics","Portable Audio & Video","Security & Surveillance","Television & Video","Car & Vehicle Electronics","Video Game Consoles & Accessories");
            //Util::$subcategories ["Health and Personal Care"] = array ("Baby & Child Care","Health Care","Household Supplies","Medical Supplies & Equipment","Personal Care","Sexual Wellness","Sports Nutrition","Stationery & Party Supplies","Vitamins & Dietary Supplements");
            Util::$subcategories ["Health and Personal Care"] = array ("Baby & Child Care");
            //Util::$subcategories ["Home and Garden"] = array ("Kids' Home Store","Kitchen & Dining","Bedding","Bath","Furniture","Home Decor","Artwork","Seasonal Decor","Heating, Cooling & Air Quality","Irons & Steamers","Vacuums & Floor Care","Storage & Organization","Cleaning Supplies");
            
            
            Util::$subcategories ["Home and Garden"] = array ("Kids' Home Store");
            Util::$subcategories ["Home Improvement"] = array("Appliances","Building Supplies","Electrical","Hardware","Kitchen & Bath Fixtures","Lighting & Ceiling Fans","Painting Supplies & Wall Treatments","Power & Hand Tools","Rough Plumbing","Safety & Security","Storage & Home Organization");
            Util::$subcategories ["Pet Supplies"] = array("Birds","Cats","Dogs","Fish & Aquatic Pets","Horses","Insects","Reptiles & Amphibians","Small Animals");
            Util::$subcategories ["Shoes and Accessories"] = array ("Womens","Mens","Girls","Boys","Handbags","Shoe Care & Accessories");
            Util::$subcategories ["Sports and Outdoors"] = array ("Action Sports","Cycling","Boating & Water Sports","Equestrian Sports","Exercise & Fitness","Golf","Hunting & Fishing","Leisure Sports & Game Room","Outdoor Gear","Paintball & Airsoft","Racquet Sports","Snow Sports","Team Sports","Other Sports","Accessories","Clothing","Fan Shop");
            Util::$subcategories ["Toys and Games"] = array ("Action Figures & Statues","Arts & Crafts","Baby & Toddler Toys","Building Toys","Dolls & Accessories","Dress Up & Pretend Play","Electronics for Kids","Games","Grown-Up Toys","Hobbies","Kids' Furniture, Decor & Storage","Learning & Education","Novelty & Gag Toys","Party Supplies","Puzzles","Sports & Outdoor Play","Stuffed Animals & Plush","Tricycles, Scooters & Wagons","Toy Remote Control & Play Vehicles");
            Util::$subcategories ["Watches"] = array ("Womens","Mens","Girls","Boys","Accessories","Novelty Watches");
            
            return Util::$subcategories[$category];
        }
	
	public static function getProducts($category="ALL", $subcategory = "ALL", $offset=0, $count=20)
	{
		$retArr = array();
                
                if($category == "ALL")
                    $sql = "SELECT * FROM products";
                else if ($subcategory == "ALL")
                    $sql = "SELECT * FROM products where `category` = '$category'";
                else
                    $sql = "select * from products where `category` = '$category' AND `subcategory` = '$subcategory'";

                 $sql = $sql . " LIMIT $offset , $count";    
                    
		$result = Util::getDB()->query($sql);
		if($result)
		{
			while ($row = mysql_fetch_assoc($result)) 
			{
				$retArr[] = $row;
			}
			mysql_free_result($result);
		}
		return $retArr;
	}

	public static function getTotalProductCount($category="ALL", $subcategory = "ALL")
	{
		if($category == "ALL")
                    $sql = "SELECT count(*) as `count` FROM products";
                else if ($subcategory == "ALL")
                    $sql = "SELECT count(*) as `count` FROM products where `category` = '$category'";
                else
                    $sql = "select count(*) as `count` from products where `category` = '$category' AND `subcategory` = '$subcategory'";
		$result = Util::getDB()->query($sql);
                
		if($result)
		{
                    $res = mysql_fetch_assoc($result);
		    return array("count"=>intval($res["count"]));
		}
		return array("count"=>0);
	}

	public static function getProductDetails($asin)
	{
		$sql = "SELECT * FROM `products` where `asin` = '$asin'";
		$result = Util::getDB()->query($sql);
		if($result)
		{
			$res = mysql_fetch_assoc($result);
                        return $res;
		}
		return;
	}

	public static function getSearchResults($category="ALL",$subcategory="ALL",$searchStr,$offset,$count)
	{
            $retArr = array();
            $searchStr = Util::getDB()->escapeString($searchStr);
            if($category == "ALL")
                $sql = "SELECT * FROM products WHERE 1";
            else if ($subcategory == "ALL")
                $sql = "SELECT * FROM products WHERE `category` = '$category'";
            else
                $sql = "select * from products WHERE `category` = '$category' AND `subcategory` = '$subcategory'";
                
            $sql = $sql . " AND MATCH (`title`, `desc`) AGAINST('$searchStr' IN BOOLEAN MODE) ORDER BY MATCH(`title`, `desc`) AGAINST('$searchStr') DESC ";
                
            $sql = $sql . " LIMIT $offset , $count";
	    $result = Util::getDB()->query($sql);
            if($result)
            {
                    while ($row = mysql_fetch_assoc($result)) 
                    {
                            $retArr[] = $row;
                    }
                    mysql_free_result($result);
            }
            return $retArr;
	}

	public static function getrecommendationInCategory($category= "ALL",$subcategory="ALL",$cart_weight=0,$cart_value=0, $offset=0,$count=20)
        {

            if($category == "ALL")
                $sql = "SELECT * FROM products WHERE 1";
            else if ($subcategory == "ALL")
                $sql = "SELECT * FROM products WHERE `category` = '$category'";
            else
                $sql = "select * from products WHERE `category` = '$category' AND `subcategory` = '$subcategory'";
            
            $sql = $sql . " AND $cart_weight +  `shipping_weight` < 20 AND $cart_value +  `final_price` > 125 AND $cart_value + `final_price` < 320";
            $sql = $sql . " LIMIT $offset , $count"; 
            $result = Util::getDB()->query($sql);
            if($result)
            {
                    while ($row = mysql_fetch_assoc($result)) 
                    {
                            $retArr[] = $row;
                    }
                    mysql_free_result($result);
            }
            return $retArr;
            
        }
        
        public static  function addItemToCart($user_id,$cart_id,$asin,$qty=1)
        {
            $sql = "select * from `cart` where cart_id = '$cart_id'";

            $result = Util::getDB()->query($sql);
            if($result && mysql_num_rows($result) > 0)
            {
                $res = mysql_fetch_assoc($result);
                mysql_free_result($result);
                $cart_items = json_decode($res["cart_items"],true);
                if(isset($cart_items[$asin]))
                    $cart_items[$asin] += $qty;
                else
                    $cart_items[$asin] = $qty;
                $cart_items_json = json_encode($cart_items);
                $sql = "UPDATE  `amasingDB`.`cart` SET  `cart_items` =  '$cart_items_json' WHERE CONVERT(`cart`.`cart_id` USING utf8 ) =  '$cart_id'";
                Util::getDB()->query($sql);
                return 1;
            }
            $cart_items = array ();
            $cart_items[$asin] = $qty;
            $cart_items_json = json_encode($cart_items);
            
            $sql = "INSERT INTO `amasingDB`.`cart` (`cart_id`, `cart_items`, `user_id`) VALUES ('$cart_id', '$cart_items_json', '$user_id')";
            Util::getDB()->query($sql);
            return 1;
        }
        
        public static function getCart($cart_id)
        {
            $sql = "select * from `cart` where cart_id = '$cart_id'";
            $result = Util::getDB()->query($sql);
            if($result)
            {
                $retArr = array();
                $res = mysql_fetch_assoc($result);
                mysql_free_result($result);
                $cart_items = json_decode($res["cart_items"]);
                foreach ($cart_items as $asin => $qty)
                {
                    $retArr[] =  array ("asin"=> $asin,"qty"=> $qty, "details" => Util::getProductDetails($asin));
                }
                return $retArr;
            }
            
        }
        
        public static function editCart($cart_id, $asin, $qty)
        {
            $sql = "select * from `cart` where cart_id = '$cart_id'";
            $result = Util::getDB()->query($sql);
            if($result && mysql_num_rows($result) > 0)
            {
                $res = mysql_fetch_assoc($result);
                mysql_free_result($result);
                $cart_items = json_decode($res["cart_items"],true);
                if($qty ==0)
                {
                    unset($cart_items[$asin]);
                }
                else
                {
                    $cart_items[$asin] = $qty;
                }
                $cart_items_json = json_encode($cart_items);
                $sql = "UPDATE  `amasingDB`.`cart` SET  `cart_items` =  '$cart_items_json' WHERE CONVERT(`cart`.`cart_id` USING utf8 ) =  '$cart_id'";
                Util::getDB()->query($sql);
                return 1;
            }
        }
}
?>