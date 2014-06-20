<?php
require_once 'global.php';

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
if (!empty($method) && !defined('NO_API_AUTO_EXECUTE')) {
    if ($method === "getMock") {
        header('Content-type: application/json');
        $file = isset($_REQUEST['file']) ? $_REQUEST['file'] : '';
        echo file_get_contents("mock/".$file);
    } else {
        new API($method);
    }
}
if(Config::isLoggerEnabled())
{
	$fp =fopen(Config::loggerFile(),"a+");
	foreach($_POST as $key =>$value)
	{
		//list($key,$value) = $node;
		fputs($fp, "$key=>$value\t");
	}
	fputs($fp,"\n");
	fclose($fp);
}
final class API {
	private $json;
	private static $excludedMethods = array('__construct', 'output');
	private $cookie_id ;
	
	public function __construct($method) {
		if (!method_exists($this, $method) || in_array($method,self::$excludedMethods)) {
			die('Method <'.$method.'> does not exist');
		}
                $this->cookie_id = isset($_COOKIE["cookie_id"])?$_COOKIE["cookie_id"]:'';
                if($this->cookie_id =='')
                {
                    $this->cookie_id = md5(uniqid(rand(), true));
                    setcookie('cookie_id',$this->cookie_id,time() + (86400 * 365));
                }
		$this->json = new stdClass();
		$this->$method();
		$this->output();
	}
	
	private function output() {

		$response = json_encode($this->json);
		// jsonp support for cross-domain api requests coming from partners ('callback' param set by jquery) 
		if (isset($_REQUEST['callback'])) {
			$response = $_REQUEST['callback'].'('.$response.')';
		}
		header('Content-type: application/json');
		echo $response;
	}
	
	private function getMethods() {
		$this->json->methods =  array_values(array_diff(get_class_methods('API'), self::$excludedMethods));
	}
        
        private function addItemToCart()
        {
            $cart_id = isset($_REQUEST["cart_id"])?$_REQUEST["cart_id"]:md5(uniqid(rand(), true));
            $asin = isset($_REQUEST["asin"])?$_REQUEST["asin"]:'';
            $qty = isset($_REQUEST["qty"])?$_REQUEST["qty"]:'1';
            if(!$asin)
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"fail", "reason"=>"Product ID must be provided");
                return;
            }
            
            $result = Util::addItemToCart($this->cookie_id,$cart_id,$asin,$qty);
            if($result == 1)
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"success");
                return;
            }
            else
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"fail", "reason"=>"Failed to add item to cart");
                return;
            }
        }
        
        private function editCart()//editCart($cart_id, $asin, $qty)
        {
            $cart_id = isset($_REQUEST["cart_id"])?$_REQUEST["cart_id"]:'';
            $asin = isset($_REQUEST["asin"])?$_REQUEST["asin"]:'';
            $qty = isset($_REQUEST["qty"])?$_REQUEST["qty"]:'';
            if(!$cart_id)
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"fail", "reason"=>"Cart ID must be provided");
                return;
            }
            if(!$asin)
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"fail", "reason"=>"Product ID must be provided");
                return;
            }
            if($qty == '')
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"fail", "reason"=>"Quantity must be provided");
                return;
            }
            $result = Util::editCart($cart_id, $asin, $qty);
            if($result == 1)
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"success");
            }
            else
            {
                $this->json = array("cart_id" => $cart_id, "status"=>"fail");
            }
        }
        
        private function getCart()
        {
            $cart_id = isset($_REQUEST["cart_id"])?$_REQUEST["cart_id"]:md5(uniqid(rand(), true));
            $this->json = Util::getCart($cart_id);
        }

        private function getUserID()
        {
            $this->json = array("user_id"=>$this->cookie_id);
        }
	

	private function getProducts()
	{
		$category =  isset($_REQUEST["category"])?$_REQUEST["category"]:'ALL';
                $subcategory = isset($_REQUEST["subcategory"])?$_REQUEST["subcategory"]:'ALL';
		$count = isset($_REQUEST["count"])?$_REQUEST["count"]:50;
		$offset =isset($_REQUEST["offset"])?$_REQUEST["offset"]:0;
		$this->json = Util::getProducts($category,$subcategory,$offset,$count);
	}
        
        private function getAllCategories()
        {
            $retArr=array();
            foreach(Util::getAllCategories() as $category)
            {
                $retArr[$category]= Util::getAllSubcategories($category);
            }
            $this->json = $retArr;
            
        }
        
        

	private function getTotalProductCount()
	{
		$category =  isset($_REQUEST["category"])?$_REQUEST["category"]:'ALL';
                $subcategory = isset($_REQUEST["subcategory"])?$_REQUEST["subcategory"]:'ALL';
                $this->json = Util::getTotalProductCount($category,$subcategory);
	}

	private function getProductDetails()
	{
		$asin =  isset($_REQUEST["asin"])?$_REQUEST["asin"]:'';
                if(!isset($asin))
                    return;
                
		if($asin)
		{
		    $this->json = Util::getProductDetails($asin);
		}
	}

	private function getSearchResults()
	{
            $category =  isset($_REQUEST["category"])?$_REQUEST["category"]:'ALL';
            $subcategory =  isset($_REQUEST["subcategory"])?$_REQUEST["subcategory"]:'ALL';
            $searchStr =  isset($_REQUEST["searchStr"])?$_REQUEST["searchStr"]:'';
            $count = isset($_REQUEST["count"])?$_REQUEST["count"]:50;
	    $offset =isset($_REQUEST["offset"])?$_REQUEST["offset"]:0;
            if(!isset($searchStr))
                return;

		$this->json = Util::getSearchResults($category,$subcategory,$searchStr,$offset,$count);
	}

	private function usrCartRedirect()
	{
		$cart =  isset($_REQUEST["cart"])?$_REQUEST["cart"]:'';
                $cart_weight =  isset($_REQUEST["cart_weight"])?$_REQUEST["cart_weight"]:'';
                $cart_value =  isset($_REQUEST["cart_value"])?$_REQUEST["cart_value"]:'';
		$stemUrl= "http://www.amazon.com/gp/aws/cart/add.html?AssociateTag=ama009-20&";
                
                if(isset($cart))
                {
                    
                    $cartArr = json_decode($cart);
                    $index=1;
                    $tail = array();
                    foreach($cartArr as $asin => $qty)
                    {
                        $tail[] = "ASIN.". $index. "=$asin";
                        $tail[] = "Quantity.". $index. "=$qty";
                        $index++;
                    }
                    $url = $stemUrl . implode("&",$tail);
                    $this->json = array("redirect_url"=>$url);
                    $sql = "INSERT INTO `amasingDB`.`redir` (`cookie_id`, `cart_url`, `cart_value`, `cart_weight`) VALUES ('$this->cookie_id', '$url', $cart_value, $cart_weight)";
                    $result = Util::getDB()->query($sql);
                }
        }
        
        private function log()
        {
            //'visit', 'cat_change', 'search', 'add_item', 'remove_item', 'inc_qty', 'dec_qty', 'checkout', 'prod_details', 'scroll'
            $event =  isset($_REQUEST["event"])?$_REQUEST["event"]:'';
            $data = isset($_REQUEST["data"])?$_REQUEST["data"]:'';
            $sql = "INSERT INTO `amasingDB`.`logs` (`cookie_id`, `event`, `data`) VALUES ('$this->cookie_id', '$event', '$data')";
            $result = Util::getDB()->query($sql);
            $this->json = "succes";
        }
        
        private function getrecommendationInCategory()//cart_weight, cart_value)
        {
            $category =  isset($_REQUEST["category"])?$_REQUEST["category"]:'ALL';
            $subcategory =  isset($_REQUEST["subcategory"])?$_REQUEST["subcategory"]:'ALL';
            $cart_weight =  isset($_REQUEST["cart_weight"])?$_REQUEST["cart_weight"]:0;
            $cart_value = isset($_REQUEST["cart_value"])?$_REQUEST["cart_value"]:0;
            $count = isset($_REQUEST["count"])?$_REQUEST["count"]:20;
	    $offset =isset($_REQUEST["offset"])?$_REQUEST["offset"]:0;
            if($cart_value ==0 || $cart_weight ==0)
                return;
            $this->json = Util::getrecommendationInCategory($category,$subcategory,$cart_weight,$cart_value,$offset,$count);
        }
        
        private function stopWorkerInstances()
        {
            $delay = isset($_REQUEST["delay"])?$_REQUEST["delay"]:'300'; //default is 5 mins
            //passthru("/usr/bin/php /var/www/html/scripts/aws-start-instance/stop_workers.php $delay >/dev/null 2>&1 &");
            $this->json = "Terminate initiated!";
        }
        
        private function restartWorkerInstances()
        {
            $delay = isset($_REQUEST["delay"])?$_REQUEST["delay"]:'5'; //default is 5 mins
            //passthru("/usr/bin/php /var/www/html/scripts/aws-start-instance/restart_workers.php $delay >/dev/null 2>&1 &");
            $this->json = "Restart initiated!";
        }

	

}
?>