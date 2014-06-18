<?php
error_reporting(E_ALL);
ini_set('display_errors','On');


$dbhost= "localhost";
$dbusername="prazdbusr";
$dbpass="iam2good";
$dbname = "amasingDB";
$connection = null;


function connectDB()
{
    global $connection, $dbhost, $dbusername, $dbpass,$dbname;
    $connection = mysql_connect("$dbhost","$dbusername","$dbpass");
    if (!$connection)
    {
        die('Could not connect: ' . mysql_error());
    }
    else
    {
        $dbcheck = mysql_select_db("$dbname");
        if (!$dbcheck) {
            echo mysql_error();
            return 0;
        }else{
            return 1;
        }    
    
    }
}


// Main program starts here //

$result= connectDB();
if($result ==0)
{
    
    echo "connection failed";
    exit;    
}
$counter =0;
$error =0;
$filenames = glob("/vol/jsonfiles/*.json");

$fperr = fopen("errors.sql","w");

foreach ($filenames  as $filename)
{
    $fp = fopen ($filename,"r");
   
    if(!$fp){
        echo "could not open file";
        exit;
    }
    $sql = "INSERT INTO `$dbname`.`products` (`asin`, `rank`,`title`, `desc`, `images`, `category`, `subcategory`, `rating`, `num_ratings`, `org_price`, `discount`, `final_price`, `shipping_weight`,`height`,`width`,`length`) VALUES ";
    while(!feof($fp))
    {
        echo ".";
        $line = fgets($fp);   
        if($line)
        {
            $counter++;
           // continue;
            
            $obj= json_decode($line);
            $title = mysql_real_escape_string($obj->title);
            $desc = mysql_real_escape_string($obj->desc);
            $images = mysql_real_escape_string(implode(", ", $obj->images));
            $category = mysql_real_escape_string($obj->category);
            $subcategory = mysql_real_escape_string($obj->subcategory);
            $obj->final_price = str_replace(",","",$obj->final_price);
            $obj->org_price = str_replace(",","",$obj->org_price);
            $obj->discount = str_replace(",","",$obj->discount);
            
            
            
            if(!isset($obj->dimensions))
            {
                $height =0;
                $width=0;
                $length=0;
            }
            else
            {
                if(count(explode(' x ',$obj->dimensions))== 3)
                {
                
                list($height,$width,$length_with_units) = explode(" x ",$obj->dimensions);
                list($length,$units) = explode(" ",$length_with_units);
                }
                else
                {
                    list($height,$length_with_units) = explode(" x ",$obj->dimensions);
                    list($length,$units) = explode(" ",$length_with_units);
                    $width = 1;
                }
            }
            $weight = isset($obj->weight)?trim($obj->weight):0;
            if(isset($obj->weight))
            {
                list($weight,$units) = explode(" ",$weight);
                if(trim($units) == "ounces")
                { 
                    $weight = $weight * 0.0625;
                }
            }
            $rating = isset($obj->rating)?$obj->rating:0;
            if(!$rating)
                $rating =0;
            if(strstr($obj->final_price,"-"))
                list($final_price,$other) = explode(' ',$obj->final_price); // this is to fix the bug in price range
            else
                $final_price = $obj->final_price;
            
            $num_ratings = isset($obj->num_ratings)?$obj->num_ratings:0;
            $values = "('$obj->asin', $obj->rank, '$title', '$desc', '$images', '$category', '$subcategory', $rating, $num_ratings, $obj->org_price, $obj->discount, $final_price, $weight,$height,$width,$length)"; 
            $sql_exec = $sql . $values ." ON DUPLICATE KEY UPDATE `rank`= $obj->rank, `images`='$images',`title`= '$title' , `desc`= '$desc', `rating` = $rating, `num_ratings`= $num_ratings, `org_price`= $obj->org_price, `discount`= $obj->discount,`final_price`= $final_price,`shipping_weight`=$weight, `height`=$height, `width`=$width,`length`=$length";
            $result = mysql_query($sql_exec);
          
            if (!$result) {
                $error ++;
                
                fputs($fperr,"$sql_exec \n\n");
                //echo '\n\nInvalid query: ' . mysql_error() ." query:\n\n $sql_exec \n\n";
                //exit;
            }
        }                
    }
    
    fclose($fp);
}

echo " Total object count: $counter Error: $error \n";

?>