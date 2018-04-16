<?php
session_start();
//TODO - add daily diary notes in db, show up on mouse over of graph data point?
//FIX - calendar starts/highlights day
//TODO - daily goals add burned calories removed (and add note at bottom)
//TODO - create meal option to add to diary
//TODO - add daily macro pie chart
//TODO - add BP in stats popup
//TODO - add BP graph
//TODO - add login
require_once 'inc/functions.php';
require_once 'inc/db_connect.php';


$valid_passwords = array ("webuser" => "webpass");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

#To add crappy login security, uncomment below and comment out the forced "true".
//$loggedin = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);
$loggedin = True;

if (get_get_var('login')==1 and !$loggedin and !isset($_SESSION['realm'])) {
    $_SESSION['realm'] = mt_rand(1, 1000000000);
    header('WWW-Authenticate: Basic realm="'.$_SESSION['realm'].'"');
    header('HTTP/1.0 401 Unauthorized');
    $loggedin = False;
}

$db = new dbconn();


//if servingsize is blank, set to 1, else servingsize

switch (isset($_POST)){
    case isset($_POST["dbaddstats"]):
        dbaddstats(); break;
    case isset($_POST["dbcopymeal"]):
        dbcopymeal(); break;
    case isset($_POST["diarymodify"]):
        modifydiaryitem(); break;
    case isset($_POST["create_food"]):
        createfood(); break;
    case isset($_POST["delete_food"]):
        deletefood(); break;
    case isset($_POST["update_food"]):
        updatefood(); break;
    case isset($_POST["diarysubmit"]):
        diary(addfoodtodb()); break;
    case isset($_POST["addfoodtodiary"]):
        diary(adddiaryitem()); break;
    case isset($_POST["deletefoodfromdiary"]):
        diary(deletefoodfromdb()); break;
    case isset($_POST["unlockdiary"]):
        diary(diarylock(false)); break;
    case isset($_POST["lockdiary"]):
        diary(diarylock(true)); break;
    default:
        diary(null);
        //modifydiaryitem();
}
//diary($msg);




function diary($msg){
    global $db, $loggedin;
    $date = validateDate(get_get_var('date')) ? get_get_var('date') : (new DateTime())->format('Y-m-d');
    $sql = 'SELECT locked FROM health.diarydate WHERE date=?';
    $lockedres = $db->fetcharray($sql, array($date), 's');
    
    $locked = (sizeof($lockedres) > 0) ? ($lockedres[0]['locked']) : false;
    echo diaryheader();
    echo calendarheader($date, $locked, $loggedin);
    echo listmeals($date, $locked, $loggedin);
    echo diarysummation($date);
    if (isset($msg)){ echo '<script language="javascript">alert("'.$msg.'");</script>'; }
    echo footer($date);
    }

function dbaddstats(){
    global $db;
    $user = get_post_var('user');
    $date = get_post_var('date');
    $weight = get_post_var('weight');
    
    if (!validateDate($date) or !is_digit($user) or !is_numeric($weight)){
        http_response_code(400);
        echo ('Diary stats modification failed'); 
    }
    else{
        //$sql = "INSERT INTO health.weight (date, weight) VALUES (?, ?)";
        //$db->insert($sql, array($date, $weight), 'ss');
        echo "user=$user, date=$date, weight=$weight";
        
        $sql = 'SELECT idweight FROM health.weight WHERE date=?';
        $statsres = $db->fetcharray($sql, array($date), 's');
        if (sizeof($statsres)>0){
            $sql = 'UPDATE health.weight SET weight=? WHERE date=?';
        }
        else{
            $sql = "INSERT INTO health.weight (weight, date) VALUES (?, ?)";
        }
        $db->update($sql, array($weight, $date), 'ss');
    }
}
function dbcopymeal(){
    global $db;
    $user = get_post_var('user');
    $fromdate = get_post_var('fromdate');
    $todate = get_post_var('todate');
    $frommeal = get_post_var('frommeal');
    $tomeal = get_post_var('tomeal');
    //$tomeal = 'Breakfast';
    $locked=true;

    if (!validateDate($fromdate) or !validateDate($todate) or !is_digit($user)){
        http_response_code(400);
    }
    
    else{
    
        $sql = "SELECT foodid, servings FROM health.fooddiary fd
                JOIN health.foodtime ft on fd.meal = ft.idfoodtime
                WHERE date=? and ft.meal = ?";
        $fromres = $db->fetcharray($sql, array($fromdate, $frommeal), 'ss');
        
        $sql = "SELECT idfoodtime FROM health.foodtime WHERE meal=?";
        $tomealres = $db->fetcharray($sql, array($tomeal), 's');
        
        //verify the to-date isn't a locked day
        $sql = "SELECT locked FROM health.diarydate WHERE date=?";
        $todateres = $db->fetcharray($sql, array($todate), 's');
        if (sizeof($todateres)>0){
            $locked = $todateres[0]['locked'];
        }
        else { $locked=false; } //day wasn't found, so it's not locked
        
        if ((sizeof($fromres)>0) and (sizeof($tomealres)>0) and ($locked==false)){
            $sql = "INSERT INTO health.fooddiary (date, meal, foodid, servings) VALUES (?, ?, ?, ?)";
            foreach($fromres as $key => $fromresrow){
                $db->update($sql, array($todate, $tomealres[0]['idfoodtime'], $fromresrow['foodid'], $fromresrow['servings']), 'ssss');
            }
            
            //$sql = 'UPDATE health.weight SET weight=? WHERE date=?';
            //$sql = "INSERT INTO health.weight (weight, date) VALUES (?, ?)";
        }
        else{
            //nothing to copy, shouldn't appen
            http_response_code(400);
            
        }
        if ($locked){
            http_response_code(400);
        }
        //$db->update($sql, array($weight, $date), 'ss');
    }
    
    
}  
function adddiaryitem(){
    global $header, $db;
    $servingsize = get_post_var('servingsize')=='' ? '1' : get_post_var('servingsize');
    $brand_product = get_post_var('brand_product');
    $meal = get_post_var('meal');
    $date = get_post_var('date');
    $foodid = '0';
    if (!validateDate($date) or !($meal>0 and $meal<=4) or !is_numeric($servingsize)){
        return ('Diary add modification failed'); 
    }
    
    $sql = "SELECT meal FROM health.foodtime WHERE idfoodtime=?";
    $mealres = $db->fetcharray($sql, array($meal), 's');
    $mealtext = $mealres[0]['meal'];
    $prettydate = date('l, F j, Y', strtotime($date));
    echo $header;
    echo "<h3>Add Food To $mealtext Diary for $prettydate</h3>"."\n";
    echo "<form action=\"\" method=\"post\" autocomplete=\"off\" id=\"adddiaryform\" name=\"adddiaryform\">\n";
    echo "<div id='nutrition'><table>
  <tr><td>Item Name:<td><input type='text' autofocus='autofocus' autocomplete='off' onkeyup=\"autocomplet('brand_product')\" size='20' maxlength='100' name='brand_product' value='$brand_product'><ul id='food_list_brand_product'></ul>
  <tr><td>Serving Size:<td><input type='text' onkeyup='setservingsize()' size='10' maxlength='100' name='servingsize' value='$servingsize'>
  <tr><td><input type='hidden' name='foodid' value='$foodid)'>"
            . "<input type='hidden' name='meal' value='$meal'>"
            . "<input type='hidden' name='date' value='$date'>"
            . "<input type='hidden' name='servnorm' value='1'>
  <td><input type='submit' name='diarysubmit' value='Add Food To Diary'>
 </table></div></form>";



    echo '<br><p align="left"><span id="nutritionfacts"></span></p>';
}
function modifydiaryitem(){
    global $db;
    $diaryid = get_post_var('diaryid');
    $servings = get_post_var('servings');
    if (!is_numeric($servings) or !is_digit($diaryid)){
        echo 'invalid input: '.$diaryid.' '.$servings;
        http_response_code(400);
    }
    $sql = 'UPDATE health.fooddiary SET servings=? WHERE diaryid=?';
    $db->update($sql, array($servings, $diaryid), 'ss');
}

function deletefoodfromdb(){
    global $db;
    $diaryid = get_post_var('diaryid');
    $date = get_post_var('date');
    if (!validateDate($date) or !is_digit($diaryid)){
        return ('Diary delete modification failed');
    }
    $sql = "DELETE FROM health.fooddiary WHERE diaryid=? AND date=?";
    $db->delete($sql, array($diaryid, $date), 'ss');
    return (null);
}
function addfoodtodb(){
    global $db;
    $servingsize = get_post_var('servnorm');
    $foodid = get_post_var('foodid');
    $meal = get_post_var('meal');
    $date = get_post_var('date');
    //verify all post vars are good
    if (!validateDate($date) or !is_digit($foodid) or !($meal>0 and $meal<=4) or !is_numeric($servingsize)){
        return ('Diary addtodb modification failed');
    }

    diarylock(false);
    $sql = "INSERT INTO health.fooddiary (date, meal, foodid, servings) VALUES (?, ?, ?, ?)";
    $db->insert($sql, array($date, $meal, $foodid, $servingsize), 'ssss');
    return (null);
}
//Make diary locked or unlocked for this day
function diarylock($lock) {
    global $db;
    $date = get_post_var('date');
    if (!validateDate($date)){
        return ('Diary lock modification failed');
    }
    $sql = 'SELECT iddiarydate FROM health.diarydate WHERE date=?';
    $lockedres = $db->fetcharray($sql, array($date), 's');
    if (sizeof($lockedres)>0){
        $sql = 'UPDATE health.diarydate SET locked=? WHERE date=?';
    }
    else{
        $sql = 'INSERT INTO health.diarydate (locked, date) VALUES (?, ?)';
    }
    $db->update($sql, array($lock, $date), 'ss');
    return (null);
}

function createfood(){
    global $foodid, $brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
            $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
            $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron, 
            $confirmed, $cat1, $cat2, $cost, $depricated, $db;
    readfood();   

    //make sure brand/product doesn't exist   or product = ? , $product
    $sql = 'SELECT foodid FROM health.food WHERE brand = ? AND product = ?';
    
    $res = $db->fetcharray($sql, array($brand, $product), 'ss');
    if (sizeof($res) > 0) {
        http_response_code(400);
        echo('Duplicate Item in Database');
    }
    else {
        //echo print_r($res);
        //echo $sql, $brand, $product;
        //error('test');
        
        //confirmed, cat1, cat2, cost, depricated added
        
        $sql = "INSERT INTO health.food (brand, product, servingsize, unit, calories, totalfat, saturatedfat, 
                polyunsaturatedfat, monounsaturatedfat, transfat, cholesterol, sodium, potassium, 
                totalcarbs, dietaryfiber, sugars, protein, vitamina, vitaminc, calcium, iron,
                confirmed, cat1, cat2, cost, depricated) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ";
        $insertarray = array($brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
                $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
                $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron,
                $confirmed, $cat1, $cat2, $cost, $depricated);
        $db->insert($sql, $insertarray, 'ssssssssssssssssssssssssss');
        unset($res);
        $sql = 'SELECT * FROM health.food WHERE brand = ? AND product = ?';
        $res = $db->fetcharray($sql, array($brand, $product), 'ss');
        if (sizeof($res) == 1) {
            $foodid = $res[0]['foodid'];
            $brand = $res[0]['brand'];
            $product = $res[0]['product'];
            $response = new stdClass();
            $response->message=webdecode($brand).' '.webdecode($product).' Added';
            $response->foodid=$foodid;
            header("Content-Type: application/json", true);
            //http_response_code(200);
            echo json_encode($response, JSON_NUMERIC_CHECK);
        
        
            //echo(webencode($brand).' '.webencode($product).' Added');
        }
        else {
            http_response_code(400);
            echo('Item Creation Failed');
        }
        
    }
    
}

function updatefood(){
    global $foodid, $brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
            $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
            $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron, 
            $confirmed, $cat1, $cat2, $cost, $depricated, $db;
    readfood();
    $sql="UPDATE health.food SET brand=?, product=?, servingsize=?, unit=?, calories=?, totalfat=?, saturatedfat=?, 
                polyunsaturatedfat=?, monounsaturatedfat=?, transfat=?, cholesterol=?, sodium=?, potassium=?, 
                totalcarbs=?, dietaryfiber=?, sugars=?, protein=?, vitamina=?, vitaminc=?, calcium=?, iron=?,
                confirmed=?, cat1=?, cat2=?, cost=?, depricated=? WHERE foodid=?";
    $updatearray = array($brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
                $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
                $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron, 
                $confirmed, $cat1, $cat2, $depricated, $foodid);
    $db->update($sql, $updatearray, 'sssssssssssssssssssssssssss');
    $response = new stdClass();
    $response->message=webdecode($brand).' '.webdecode($product).' Updated';
    $response->foodid=$foodid;
    header("Content-Type: application/json", true);
            //http_response_code(200);
    echo json_encode($response, JSON_NUMERIC_CHECK);
    //echo $brand.' '.$product.' Updated';
}

function deletefood(){
    
    global $foodid, $brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
            $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
            $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron,
            $confirmed, $cat1, $cat2, $cost, $depricated, $db;
    readfood();
    if ($foodid>0){
        $sql = "DELETE FROM health.food WHERE foodid=?";
        $db->delete($sql, array($foodid), 's');
        unset($foodid, $brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
                $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
                $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron,
                $confirmed, $cat1, $cat2, $cost, $depricated);
        $response = new stdClass();
            $response->message=webdecode($brand).' '.webdecode($product).' Deleted';
            $response->foodid='';
            header("Content-Type: application/json", true);
            //http_response_code(200);
            echo json_encode($response, JSON_NUMERIC_CHECK);
        //echo $brand.' '.$product.' Deleted';
    }
    else{
        http_response_code(400);
        echo('FoodID '.$foodid.' not found to delete.');
    }
    

}

function readfood() {
    global $foodid, $brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
            $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
            $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron,
            $confirmed, $cat1, $cat2, $cost, $depricated;
    $foodid = get_post_var('foodid');
    $brand = get_post_var('brand');
    $product = get_post_var('product');
    $servingsize = get_post_var('servingsize');
    $unit = get_post_var('unit');
    $calories = get_post_var('calories');
    $totalfat = get_post_var('totalfat');
    $saturatedfat = get_post_var('saturatedfat');
    $polyunsaturatedfat = get_post_var('polyunsaturatedfat');
    $monounsaturatedfat = get_post_var('monounsaturatedfat');
    $transfat = get_post_var('transfat');
    $cholesterol = get_post_var('cholesterol');
    $sodium = get_post_var('sodium');
    $potassium = get_post_var('potassium');
    $totalcarbs = get_post_var('totalcarbs');
    $dietaryfiber = get_post_var('dietaryfiber');
    $sugars = get_post_var('sugars');
    $protein = get_post_var('protein');
    $vitamina = get_post_var('vitamina');
    $vitaminc = get_post_var('vitaminc');
    $calcium = get_post_var('calcium');
    $iron = get_post_var('iron');
    $confirmed = get_post_var('confirmed');
    $cat1 = get_post_var('cat1');
    $cat2 = get_post_var('cat2');
    $cost = get_post_var('cost');
    $depricated = get_post_var('depricated');
    
    
}
?>

