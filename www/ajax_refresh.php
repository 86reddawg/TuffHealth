<?php

require_once 'inc/functions.php';
require_once 'inc/db_connect.php';
$db = new dbconn();
// PDO connect *********
function connect() {
    return new PDO('mysql:host=localhost;dbname=health', 'username', 'password', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
}

$pdo = connect();

$f = get_get_var('f');

$id_keyword = get_post_var('id_keyword');
$brand_keyword = get_post_var('brand_keyword');
$product_keyword = get_post_var('product_keyword');
$brand_product_keyword = get_post_var('brand_product_keyword');


global $cals_in_carbs, $cals_in_protein, $cals_in_fat;
if ($f == 'main'){
    global $test;

    
    $user = getuserdata();
    $sql = "
SELECT @x=0, 
    big.date, 
    ROUND(IF(weight IS NOT NULL, @x:=weight, @x),1) as dailyweight, 
    ROUND(calsum,1) as calsum,
    ROUND(carsum,1) as carsum,
    ROUND(fatsum,1) as fatsum,
    ROUND(prosum,1) as prosum,
    ROUND(sodsum,1) as sodsum,
    ROUND(sugsum,1) as sugsum,
    ROUND(cholsum,1) as cholsum
FROM (
    (SELECT IFNULL(w.date, fd.date) as date, w.weight, 
        sum(servings*calories) as calsum,
        sum(servings*totalcarbs) as carsum,
        sum(servings*totalfat) as fatsum,
        sum(servings*protein) as prosum,
        sum(servings*sodium) as sodsum,
        sum(servings*sugars) as sugsum,
        sum(servings*cholesterol) as cholsum
    FROM health.fooddiary AS fd
    JOIN health.food f ON fd.foodid=f.foodid
    LEFT JOIN health.weight AS w ON w.date = fd.date
    GROUP BY date
    ORDER BY date ASC)
UNION
    (SELECT IFNULL(w.date, fd.date) as date, w.weight, 
        sum(servings*calories) as calsum, 
        sum(servings*totalcarbs) as carsum,
        sum(servings*totalfat) as fatsum,
        sum(servings*protein) as prosum,
        sum(servings*sodium) as sodsum,
        sum(servings*sugars) as sugsum,
        sum(servings*cholesterol) as cholsum
    FROM health.fooddiary AS fd
    JOIN health.food f ON fd.foodid=f.foodid
    RIGHT JOIN health.weight AS w ON w.date = fd.date
    GROUP BY date
    ORDER BY date ASC)) as big
LEFT JOIN health.diarydate dd ON dd.date=big.date
WHERE dd.locked='1' OR dd.locked IS NULL AND dd.date>'2016-10-27'
ORDER BY date";
    $graphres = $db->fetcharray($sql, array(), '');
    $data = new stdClass();
    $date = array();
    $weight = array();
    $calories = array();
    $carbs = array();
    $fat = array();
    $protein = array();
    $sodium = array();
    $sugar = array();
    $cholesterol = array();
    $caloriespct = array();
    $carbspct = array();
    $fatpct = array();
    $proteinpct = array();
    $sodiumpct = array();
    $sugarpct = array();
    $othercarbscals = array();
    $fatcals = array();
    $proteincals = array();
    $sugarcals = array();
    
    
    $i=0;
    if (sizeof($graphres) > 0) {
        foreach($graphres as $key => $row){
            //get UNIX time to javascript epoch time
            $date[] = strtotime($row['date'])*1000;
            //if missing value, hold over from last value
            $weight[] = $row['dailyweight']!=null ? $row['dailyweight'] : end($weight);
            //get calories in percent of normal TODO - do this in sql statement
            $calories[] = $row['calsum']!=null ? +round($row['calsum'],1) : null; ///22.50
            $caloriespct[] = $row['calsum']!=null ? +round($row['calsum']/($user['goalcals']/100),1) : null;
            $carbs[] = $row['carsum']!=null ? +round($row['carsum'],1) : null;
            $carbspct[] = $row['carsum']!=null ? +round($row['carsum']/($user['goalcarbs']/100),1) : null;
            $fat[] = $row['fatsum']!=null ? +round($row['fatsum'],1) : null;
            $fatpct[] = $row['fatsum']!=null ? +round($row['fatsum']/($user['goalfat']/100),1) : null;
            $protein[] = $row['prosum']!=null ? +round($row['prosum'],1) : null;
            $proteinpct[] = $row['prosum']!=null ? +round($row['prosum']/($user['goalprotein']/100),1) : null;
            $sodium[] = $row['sodsum']!=null ? +round($row['sodsum'],1) : null;
            $sodiumpct[] = $row['sodsum']!=null ? +round($row['sodsum']/($user['goalsodium']/100),1) : null;
            $sugar[] = $row['sugsum']!=null ? +round($row['sugsum'],1) : null;
            $sugarpct[] = $row['sugsum']!=null ? +round($row['sugsum']/($user['goalsugar']/100),1) : null;
            $cholesterol[] = $row['cholsum']!=null ? +round($row['cholsum'],1) : null;
            $totalcarbscals[] = $row['carsum']!=null ? +round($row['carsum']*4,1) : null;
            $sugarcals[] = $row['sugsum']!=null ? +round($row['sugsum']*4,1) : null;
            $othercarbscals[] = $row['carsum']!=null ? +round(($row['carsum']-$row['sugsum'])*4,1) : null;
            $fatcals[] = $row['fatsum']!=null ? +round($row['fatsum']*9,1) : null;
            $proteincals[] = $row['prosum']!=null ? +round($row['prosum']*4,1) : null;
            
            
            //echo $row['date'].','.$row['weight']."\n";
        }
        
        //keep null calorie data from affecting average with array_filter (could also drop calories=0)
        $avgcal = array_sum($calories) / count (array_filter($calories));
        $age = (strtotime("now")-strtotime($user['birthday']))/365/24/60/60;
        $idealweight=BMItoLB((18.5+25)/2, $user['height']);
        
        
        
        $fut=1;
        //fill future weights with null historical values, but make last value the last known weight
        $idealcalweight = array_fill(0, count($weight)-1, null);
        $idealcalweight[] = end($weight);
        $dietcalweight = array_fill(0, count($weight)-1, null);
        $dietcalweight[] = end($weight);
        $cal_to_lb = 3500;
        $everydayliketodaycals = end($calories);
        while($fut <= 1*365){
            //add 1 day in JS millisecs
            $date[] = 86400000+end($date);
            $calories[] = null;
            $caloriespct[] = null;
            $carbs[] = null;
            $carbspct[] = null;
            $fat[] = null;
            $fatpct[] = null;
            $protein[] = null;
            $proteinpct[] = null;
            $sodium[] = null;
            $sodiumpct[] = null;
            $sugar[] = null;
            $cholesterol[] = null;
            $sugarpct[] = null;
            $totalcarbscals[] = null;
            $othercarbscals[] = null;
            $fatcals[] = null;
            $proteincals[] = null;
            $sugarcals[] = null;

            
            $idealcals = dailycals($user['gender'], $idealweight, $user['height'], ($age), $user['activity']);
            $dietcals = $user['goalcals'];
            
            $sustaincals = dailycals($user['gender'], end($weight), $user['height'], ($age+$fut/365), $user['activity']);
            $idealsustaincals = dailycals($user['gender'], end($idealcalweight), $user['height'], ($age+$fut/365), $user['activity']);
            $dietsustaincals = dailycals($user['gender'], end($dietcalweight), $user['height'], ($age+$fut/365), $user['activity']);
            
            
            
            $weightdiff = (($sustaincals-$everydayliketodaycals)/$cal_to_lb);
            $idealweightdiff = (($idealsustaincals-$idealcals)/$cal_to_lb);
            $dietweightdiff = (($dietsustaincals-$dietcals)/$cal_to_lb);
            
            
            $weight[] = round(end($weight)-$weightdiff,2);
            $idealcalweight[] = round(end($idealcalweight)-$idealweightdiff,2);
            $dietcalweight[] = round(end($dietcalweight)-$dietweightdiff,2);
            $fut++;
        }

        $window=21; //average the weight change over 4 weeks to smooth fluctations
        $weightavg = averager($weight, $window);
        $weightchange = differential($weightavg, $window);
        
        $maintaincals = Array();
        foreach ($weight as $key=>$val){
            $maintainage = ($date[$key]/1000-strtotime($user['birthday']))/365/24/60/60;
            $maintaincals[] = round(dailycals($user['gender'], $weight[$key], $user['height'], $maintainage, $user['activity']),1);
        }
        
        //macros - 50% carbs, 30% fat, 20% protein
        //sat fat=25, chol=300, pot=3500, fiber=38, sugar=84
        //$weightchange = averager($weight, 7, 100);
        $days=7;
        $caloriesavg = pctaverager($calories, $days, $user['goalcals']);
        $carbsavg = pctaverager($carbs, $days, $user['goalcarbs']);
        $fatavg = pctaverager($fat, $days, $user['goalfat']);
        $proteinavg = pctaverager($protein, $days, $user['goalprotein']);
        $sodiumavg = pctaverager($sodium, $days, $user['goalsodium']);
        $sugaravg = pctaverager($sugar, $days, $user['goalsugar']);
        $cholavg = pctaverager($cholesterol, $days, $user['goalcholesterol']);
        
        $cal_lb_loss = Array();
        $leftover = 0;
        
        foreach ($calories as $key=>$val){
            if (!is_null($caloriesavg[$key])){
                $caldiff = $maintaincals[$key] - $caloriesavg[$key]*17 + $leftover;
                $leftover = 0;
                //$cal_lb_loss[] = $caldiff > $cal_to_lb ? $cal_to_lb : $caldiff;
                //while ($caldiff > $cal_to_lb){
                //    $leftover += $cal_to_lb;
                //    $caldiff -= $cal_to_lb;
                //}
                //$cal_lb_loss[] = $caldiff;
                
                if ($caldiff > $cal_to_lb){
                    //$cal_lb_loss[] = $caldiff;
                    $leftover = $caldiff - $cal_to_lb;
                    $cal_lb_loss[] = $cal_to_lb;
                }
                else{
                    $leftover = $caldiff;
                    $cal_lb_loss[] = $caldiff;
                }
            }
            else{
                $cal_lb_loss[] = null;
            }
            
        }
        
        $weightjson = new stdClass();
        $weightjson->title="Weight";
        $weightjson->tickinterval=50;
        $weightjson->valueDecimals=1;
        $weightjson->ymin=150;
        
        $weightjson->name="Weight";
        $weightjson->data=$weight;
        $weightjson->unit="lbs";
        $weightjson->type="line";
        $weightjson->name1="Ideal (".round($idealcals)." Cals)";
        $weightjson->data1=$idealcalweight;
        $weightjson->unit1="lbs";
        $weightjson->type1="line";
        $weightjson->name2="Goal (".round($dietcals)." Cals)";
        $weightjson->data2=$dietcalweight;
        $weightjson->unit2="lbs";
        $weightjson->type2="line";
        $weightjson->name3="";
        $weightjson->data3=Array();
        $weightjson->type3="line";
        $weightjson->unit3="";
        $weightjson->name4="";
        $weightjson->data4=Array();
        $weightjson->type4="line";
        $weightjson->unit4="";
        $weightjson->name5="";
        $weightjson->data5=Array();
        $weightjson->type5="line";
        $weightjson->unit5="";
        $weightjson->name6="";
        $weightjson->data6=Array();
        $weightjson->type6="line";
        $weightjson->unit6="";
        

        $weightchangejson = new stdClass();
        $weightchangejson->title="Average Weekly Weight Change";
        $weightchangejson->tickinterval=1;
        $weightchangejson->valueDecimals=1;
        $weightchangejson->ymin=null;
        
        $weightchangejson->name="Weight Diff";
        $weightchangejson->data=$weightchange;
        $weightchangejson->unit="lbs";
        $weightchangejson->type="line";
        $weightchangejson->name1="";
        $weightchangejson->data1=Array();
        $weightchangejson->unit1="";
        $weightchangejson->type1="line";
        $weightchangejson->name2="";
        $weightchangejson->data2=Array();
        $weightchangejson->type2="line";
        $weightchangejson->unit2="";
        $weightchangejson->name3="";
        $weightchangejson->data3=Array();
        $weightchangejson->type3="line";
        $weightchangejson->unit3="";
        $weightchangejson->name4="";
        $weightchangejson->data4=Array();
        $weightchangejson->type4="line";
        $weightchangejson->unit4="";
        $weightchangejson->name5="";
        $weightchangejson->data5=Array();
        $weightchangejson->type5="line";
        $weightchangejson->unit5="";
        $weightchangejson->name6="";
        $weightchangejson->data6=Array();
        $weightchangejson->type6="line";
        $weightchangejson->unit6="";
        

        $caljson = new stdClass();
        $caljson->title="Calories";
        $caljson->tickinterval=1000;
        $caljson->valueDecimals=0;
        $caljson->ymin=1000;
        $caljson->ymax=4000;
        
        $caljson->name ="Maintain Cals";
        $caljson->data=$maintaincals;
        $caljson->unit="kcal";
        $caljson->type="line";
        $caljson->name1="Calories";
        $caljson->data1=$calories;
        $caljson->unit1="kcal";
        $caljson->type1="spline";
        $caljson->name2="Cal Diff";
        $caljson->data2=$cal_lb_loss;
        $caljson->type2="area";
        $caljson->unit2="kcal";
        $caljson->name3="";
        $caljson->data3=Array();
        $caljson->type3="line";
        $caljson->unit3="";
        $caljson->name4="";
        $caljson->data4=Array();
        $caljson->type4="line";
        $caljson->unit4="";
        $caljson->name5="";
        $caljson->data5=Array();
        $caljson->type5="line";
        $caljson->unit5="";
        $caljson->name6="";
        $caljson->data6=Array();
        $caljson->type6="line";
        $caljson->unit6="";
        
        $caljson->avg=+$avgcal;
        $caljson->ideal=+$idealcals;
        $caljson->diet=+$dietcals;
        
        //Daily Percentages Graph (#3)
        $dailypctjson = new stdClass();
        $dailypctjson->title="Weekly Averages (% of daily values)";
        $dailypctjson->tickinterval=50;
        $dailypctjson->valueDecimals=0;
        $dailypctjson->ymin=0;
        $dailypctjson->ymax=300;
        
        $dailypctjson->name="Calories";
        $dailypctjson->data=$caloriesavg;
        $dailypctjson->unit="%";
        $dailypctjson->type="spline";
        $dailypctjson->name1="Carbs";
        $dailypctjson->data1=$carbsavg;
        $dailypctjson->unit1="%";
        $dailypctjson->type1="spline";
        $dailypctjson->name2="Fat";
        $dailypctjson->data2=$fatavg;
        $dailypctjson->unit2="%";
        $dailypctjson->type2="spline";
        $dailypctjson->name3="Protein";
        $dailypctjson->data3=$proteinavg;
        $dailypctjson->unit3="%";
        $dailypctjson->type3="spline";
        $dailypctjson->name4="Sodium";
        $dailypctjson->data4=$sodiumavg;
        $dailypctjson->unit4="%";
        $dailypctjson->type4="spline";
        $dailypctjson->name5="Sugar";
        $dailypctjson->data5=$sugaravg;
        $dailypctjson->unit5="%";
        $dailypctjson->type5="spline";
        $dailypctjson->name6="Cholesterol";
        $dailypctjson->data6=$cholavg;
        $dailypctjson->unit6="%";
        $dailypctjson->type6="spline";
        

        //percent area for total cals:
        $areajson = new stdClass();
        $areajson->title="Calorie Macro % Breakdown";
        $areajson->tickinterval=20;
        $areajson->valueDecimals=0;
        $areajson->ymin=0;
        
        $areajson->name="Protein";
        $areajson->data=$proteincals;
        $areajson->type="areaspline";
        $areajson->unit="kcal";        
        $areajson->name1="Fat";
        $areajson->data1=$fatcals;
        $areajson->type1="areaspline";
        $areajson->unit1="kcal";
        $areajson->name2="Carbs";
        $areajson->data2=$totalcarbscals;
        $areajson->type2="areaspline";
        $areajson->unit2="kcal";
        $areajson->name3="";
        $areajson->data3=Array();
        $areajson->type3="line";
        $areajson->unit3="";
        $areajson->name4="";
        $areajson->data4=Array();
        $areajson->type4="line";
        $areajson->unit4="";
        $areajson->name5="";
        $areajson->data5=Array();
        $areajson->type5="line";
        $areajson->unit5="";
        $areajson->name6="";
        $areajson->data6=Array();
        $areajson->type6="line";
        $areajson->unit6="";
        
        $areajson->carbspct=$user['macrocarbpct'];
        $areajson->fatpct=$user['macrofatpct'];
        $areajson->proteinpct=$user['macroproteinpct'];
        
        $data->xData=$date;
        $data->datasets=array($weightjson, $weightchangejson, $caljson, $areajson, $dailypctjson);
        //$data->datasets=array($weightjson, $weightjson, $weightjson, $weightjson, $weightjson);
        header('Content-Type: text/javascript');
        echo json_encode($data, JSON_NUMERIC_CHECK);
        //echo $test;
    }
    else {
        echo 'err';
    }
    
}


else if (strlen($id_keyword)>0){ //strlen($id_keyword)>0
    $keyword = "%$id_keyword%";
    $sql = "SELECT foodid, CONCAT(brand, ' ', product) AS name FROM health.food 
        WHERE brand LIKE (:keyword) OR product LIKE (:keyword)
        ORDER BY name ASC LIMIT 0, 10";
    $query = $pdo->prepare($sql);
    $query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
    $query->execute();
    $list = $query->fetchAll();
    foreach ($list as $rs) {
        // put in bold the written text
        $food_name = preg_replace("/$id_keyword/i", "<b>\$0</b>", $rs['name']);
        // add new option
        echo '<li onclick="set_item(\''.webencode($rs['name']).'\', '.$rs['foodid'].')">'.$food_name.'</li>';
    }
}
else if ((strlen($brand_keyword)>0) AND !(strlen($product_keyword)>0)){
    $keyword = '%'.webdecode($brand_keyword).'%';
    $sql = "SELECT foodid, brand FROM health.food 
        WHERE brand LIKE (:keyword)
        GROUP BY brand
        ORDER BY brand ASC LIMIT 0, 10";
    $query = $pdo->prepare($sql);
    $query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
    $query->execute();
    $list = $query->fetchAll();
    foreach ($list as $rs) {
        // put in bold the written text
        $food_brand = preg_replace("/$brand_keyword/i", "<b>\$0</b>", $rs['brand']);
        // add new option
        echo '<li onclick="set_item(\''.webencode($rs['brand']).'\', '.$rs['foodid'].', \'brand\')">'.$food_brand.'</li>';
    }
}
else if (strlen($product_keyword)>0){
    $keyword1 = '%'.webdecode($product_keyword).'%';
    $keyword2 = '%'.webdecode($brand_keyword).'%';
    $sql = "SELECT foodid, brand, product FROM health.food 
        WHERE product LIKE (:product_keyword) AND brand LIKE (:brand_keyword)
        GROUP BY product
        ORDER BY brand, product ASC LIMIT 0, 10";
    $query = $pdo->prepare($sql);
    $query->bindParam(':product_keyword', $keyword1, PDO::PARAM_STR);
    $query->bindParam(':brand_keyword', $keyword2, PDO::PARAM_STR);
    $query->execute();
    $list = $query->fetchAll();
    foreach ($list as $rs) {
        // put in bold the written text
        $food_product = preg_replace("/$product_keyword/i", "<b>\$0</b>", $rs['product']);
        // add new option
        echo '<li onclick="set_item(\''.webencode($rs['product']).'\', '.$rs['foodid'].', \'product\')">'.$food_product.'</li>';
    }
}
else if (strlen($brand_product_keyword)>0){ //strlen($brand_product_keyword)>0 //$_POST['brand_product_keyword']
    $keyword = '%'.webdecode($brand_product_keyword).'%';
    //$brand_keyword = '%'.webdecode($_POST['brand_keyword']).'%';
    $sql = "SELECT foodid, brand, product FROM health.food 
        WHERE product LIKE (:keyword) OR brand LIKE (:keyword)
        ORDER BY brand, product ASC LIMIT 0, 10";
    $query = $pdo->prepare($sql);
    $query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
    //$query->bindParam(':brand_product_keyword', '%'.webdecode($brand_product_keyword).'%', PDO::PARAM_STR);
    //$query->bindParam(':brand_keyword', $brand_keyword, PDO::PARAM_STR);
    $query->execute();
    $list = $query->fetchAll();
    foreach ($list as $rs) {
        // put in bold the written text
        $food_product = preg_replace("/$brand_product_keyword/i", "<b>\$0</b>", $rs['brand'].' '.$rs['product']);
        // add new option
        echo '<li onclick="set_item(\''.webencode($rs['brand'].' '.$rs['product']).'\', '.$rs['foodid'].', \'brand_product\')">'.$food_product.'</li>';
    }
}
?>