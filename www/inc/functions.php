<?php
$CONFIG['dbhost'] = 'localhost';
$CONFIG['dbname'] = 'health';
$CONFIG['dbuser'] = 'username';
$CONFIG['dbpass'] = 'password';
require_once 'inc/functions.php';
require_once 'inc/db_connect.php';
$db = new dbconn();

$cals_in_carbs = 4;
$cals_in_protein = 4;
$cals_in_fat = 9;


function get_post_var($var) {
    //$name = filter_input(INPUT_POST, $var, FILTER_SANITIZE_STRING);
    $val = filter_input(INPUT_POST, $var, FILTER_SANITIZE_STRING);
    if (get_magic_quotes_gpc()){ $val = stripslashes($val); }
    return $val;
}
function get_get_var($var) {
    //$name = filter_input(INPUT_POST, $var, FILTER_SANITIZE_STRING);
    $val = filter_input(INPUT_GET, $var, FILTER_SANITIZE_STRING);
    if (get_magic_quotes_gpc()) { $val = stripslashes($val); }
    return $val;
}
function webencode($str) {
    $badtext = array("'", "\"", "(", ")", "&#39;");
    //$badtext = array('"', "(", ")");
    $goodtext = array("&#39;", '&#34;', '&#40;', '&#41;', "");
    //$goodtext = array('&#34;', '&#40;', '&#41;');
    return str_replace($badtext, $goodtext, $str);
    //return $str;
}
function webdecode($str) {
    $badtext = array("'", '"', "(", ")");
    //$badtext = array('"', "(", ")");
    $goodtext = array("&#39;", '&#34;', '&#40;', '&#41;');
    //$goodtext = array('&#34;', '&#40;', '&#41;');
    return str_replace($goodtext, $badtext, $str);
}
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
function addicon($meal, $idfoodtime, $date, $locked){
    $addhtml = 
        "<form action='' method='post'>$meal ".
        "<input type='hidden' name='meal' value='$idfoodtime'>".
        "<input type='hidden' name='date' value='$date'>".
        "<button type='submit' name='addfoodtodiary' class='clearbutton'>".
        "<i class='fa fa-plus-circle' title='Add item to $meal'></i></button></form>";
    return $locked ? $meal : $addhtml;
}
function addicon1($meal, $idfoodtime, $date, $locked){
    $prettydate = date('l, F j, Y', strtotime($date));
    $addhtml = '<div id="'.$meal.'addfood" class="popup" tabindex="-1">
    <form id="'.$meal.'addfoodform" action = "javascript:addfood()" >
        Add Food To '.$meal.'
        <input type="hidden" id="'.$meal.'addfooduser" name="user" value="1">
    </form><BR><BR>
    <button class="button popupclose" onclick="addfood(\''.$meal.'\')" >Add Food to Diary</button>
    <span align="right" class="popupclose" id="'.$meal.'addfoodClose"><button class="button" >Cancel</button></span>
</div>
<span class="popuplink" id="'.$meal.'addfoodShow">'.$meal.' <i class="fa fa-plus-circle" title="Add item to '.strtolower($meal).'"></i></span>';
    
    
    //echo "<form action=\"\" method=\"post\" autocomplete=\"off\" id=\"adddiaryform\" name=\"adddiaryform\">\n";
    //echo "<div id='nutrition'><table>
  //<tr><td>Item Name:<td><input type='text' autofocus='autofocus' autocomplete='off' onkeyup=\"autocomplet('brand_product')\" size='20' maxlength='100' name='brand_product' value='$brand_product'><ul id='food_list_brand_product'></ul>
  //<tr><td>Serving Size:<td><input type='text' onkeyup='setservingsize()' size='10' maxlength='100' name='servingsize' value='$servingsize'>
  //<tr><td><input type='hidden' name='foodid' value='$foodid)'>"
  //          . "<input type='hidden' name='meal' value='$meal'>"
  //          . "<input type='hidden' name='date' value='$date'>"
  //          . "<input type='hidden' name='servnorm' value='1'>
  //<td><input type='submit' name='diarysubmit' value='Add Food To Diary'>
 //</table></div></form>";
   // echo '<br><p align="left"><span id="nutritionfacts"></span></p>';
    return $locked ? $meal : $addhtml;
    //return $html;
}
//copyicon($mealrow['meal'], $mealrow['idfoodtime'], $date, $locked)
function copyicon($meal, $date, $locked){
    $breakfastchecked = $meal=='Breakfast' ? 'checked' : '';
    $lunchchecked = $meal=='Lunch' ? 'checked' : '';
    $dinnerchecked = $meal=='Dinner' ? 'checked' : '';
    $snackschecked = $meal=='Snacks' ? 'checked' : '';
    $addhtml = 
'<div id="'.$meal.'modmeals" class="popup" tabindex="-1">
    <form id="'.$meal.'modmealsform" action = "javascript:copymeals()" >
        Copy '.$meal.' to date: <input type="text" class="initfocus" id="'.$meal.'modmealstodate" name="date" size="10" autocomplete="off" autofocus="autofocus" value="'.$date.'"><br>
        <input type="radio" name="'.$meal.'modmealstomeal" value="Breakfast" '.$breakfastchecked.'>Breakfast
        <input type="radio" name="'.$meal.'modmealstomeal" value="Lunch" '.$lunchchecked.'>Lunch
        <input type="radio" name="'.$meal.'modmealstomeal" value="Dinner" '.$dinnerchecked.'>Dinner
        <input type="radio" name="'.$meal.'modmealstomeal" value="Snacks" '.$snackschecked.'>Snacks
        <input type="hidden" id="'.$meal.'modmealsuser" name="user" value="1">
        <input type="hidden" id="'.$meal.'modmealsfromdate" name="date" value="'.$date.'">
        <input type="hidden" id="'.$meal.'modmealsfrommeal" name="meal" value="'.$meal.'">
    </form><BR><BR>
    <button class="button popupclose" onclick="copymeals(\''.$meal.'\')" >Copy Meal</button>
    <span align="right" class="popupclose" id="'.$meal.'modmealsClose"><button class="button" >Cancel</button></span>
</div>
<span class="popuplink" id="'.$meal.'modmealsShow"><i class="fa fa-calendar-plus-o" title="Duplicate '.strtolower($meal).' on another day"></i></span>';
//<div id="'.$meal.'modcontent">
    return $locked ? "" : $addhtml;
}
function deleteicon($diaryid, $date, $locked) {
    $deletehtml = 
        "<td><form action='' method='post'>".
        "<input type='hidden' name='diaryid' value='$diaryid'>".
        "<input type='hidden' name='date' value='$date'>".
        "<button type='submit' name='deletefoodfromdiary' class='clearbutton'>".
        "<i class='fa fa-minus-circle' title='Delete item'></i>".
        "</button></form></td>";
    return $locked ? '' : $deletehtml;
}
function editableicon($date, $locked, $loggedin){
    
    $status = $locked ? 'unlockdiary' : 'lockdiary';
    $icon = $locked ? 'fa-lock' : 'fa-unlock';
    if ($loggedin) {
        return "<form action='' method='post'>".
            "<input value='$date' id='tsDte' type='hidden' >".
            "<i class='fa fa-calendar fa-2x' onclick='tsDatePickerClick()' title='Select date'></i>&nbsp&nbsp".
            "<input type='hidden' name='date' value='$date'>".
            "<button type='submit' name='$status' class='clearbutton'>".
            "<span id='diaryToggle'><i class='fa $icon fa-2x' title='Toggle ability to edit day'></i></span>".
            "</button>
            <span id='statsShow' class='popuplink'><i class='fa fa-heartbeat fa-2x' title='Add measurements'></i></span>
            <span id='crudfoodShow' class='popuplink'><i class='fa fa-cart-plus fa-2x' title='Modify food database'></i></span>
            </form>"; 
    }
    else {
        return "<form action='' method='post'>".
            "<input value='$date' id='tsDte' type='hidden' >".
            "<i class='fa fa-calendar fa-2x' onclick='tsDatePickerClick()' title='Select date'></i>&nbsp&nbsp".
            "<input type='hidden' name='date' value='$date'>".
            "</form>"; 
            //"<button type='submit' name='$status' class='clearbutton'>".
            //"<span id='diaryToggle'><i class='fa $icon fa-2x' title='Toggle ability to edit day'></i></span>".
            //"</button>
            //<span id='statsShow' class='popuplink'><i class='fa fa-heartbeat fa-2x' title='Add measurements'></i></span>
            //<span id='crudfoodShow' class='popuplink'><i class='fa fa-cart-plus fa-2x' title='Modify food database'></i></span>
            //</form>"; 
    }
}
function BMItoLB($bmi, $height){ //convert BMI to lbs
    $BMI_C=703;
    return $bmi*pow($height,2)/$BMI_C;
}
function weightranges($height){
    return array(
    0 => array( "color"=>"rgba(255,000,000,.2)", 
                "textcolor"=>"rgba(255,000,000,1)",
                "min"=>+round(BMItoLB(00.0, $height),1), 
                "max"=>+round(BMItoLB(15.0, $height),1), 
                "desc"=>"Very Severely Underweight"),
    1 => array( "color"=>"rgba(255,131,000,.2)", 
                "textcolor"=>"rgba(255,131,000,1)", 
                "min"=>+round(BMItoLB(15.0, $height),1), 
                "max"=>+round(BMItoLB(16.0, $height),1), 
                "desc"=>"Severely Underweight"),
    2 => array( "color"=>"rgba(255,255,000,.2)", 
                "textcolor"=>"rgba(255,255,000,1)",
                "min"=>+round(BMItoLB(16.0, $height),1), 
                "max"=>+round(BMItoLB(18.5, $height),1), 
                "desc"=>"Underweight"),
    3 => array( "color"=>"rgba(000,255,000,.2)", 
                "textcolor"=>"rgba(000,255,000,1)",
                "min"=>+round(BMItoLB(18.5, $height),1), 
                "max"=>+round(BMItoLB(25.0, $height),1), 
                "desc"=>"Healthy Weight"),
    4 => array( "color"=>"rgba(255,255,000,.2)", 
                "textcolor"=>"rgba(255,255,000,1)", 
                "min"=>+round(BMItoLB(25.0, $height),1), 
                "max"=>+round(BMItoLB(30.0, $height),1), 
                "desc"=>"Overweight"),
    5 => array( "color"=>"rgba(255,131,000,.2)", 
                "textcolor"=>"rgba(255,131,000,1)", 
                "min"=>+round(BMItoLB(30.0, $height),1), 
                "max"=>+round(BMItoLB(35.0, $height),1), 
                "desc"=>"Moderately Obese"),
    6 => array( "color"=>"rgba(255,000,000,.2)", 
                "textcolor"=>"rgba(255,000,000,1)", 
                "min"=>+round(BMItoLB(35.0, $height),1), 
                "max"=>+round(BMItoLB(40.0, $height),1), 
                "desc"=>"Severely Obese"),
    7 => array( "color"=>"rgba(255,000,000,.4)", 
                "textcolor"=>"rgba(255,000,000,1)", 
                "min"=>+round(BMItoLB(40.0, $height),1), 
                "max"=>+round(BMItoLB(1000.0, $height),1), 
                "desc"=>"Extremely Obese")
    );
}
function dailycals($gender, $weight, $height, $age, $activity){
    //daily cals differ for males and females by +5 or -161 in equation
    $gendermod = ($gender='male') ? 5 : -161;
    $lb_to_kg = 0.45359237;
    $in_to_cm = 2.54;
    //Mifflin - St Jeor Formula
    return (10*$weight*$lb_to_kg + 6.25*$height*$in_to_cm - 5*$age + $gendermod)*$activity;
    
}
function is_digit($digit) {
    if(is_int($digit)) {
        return true;
    } 
    elseif(is_string($digit)) {
        return ctype_digit($digit);
    }
    else {
        // booleans, floats and others
        return false;
    }
}
function calendarheader($date, $locked, $loggedin) {
    $dateminusone = date('Y-m-d', strtotime('-1 day', strtotime($date)));
    $dateplusone = date('Y-m-d', strtotime('+1 day', strtotime($date)));
    $prettydate = date('l, F j, Y', strtotime($date));
    $editableicon = editableicon($date, $locked, $loggedin);
    $loggedinlink = $loggedin ? '&login=1' : '';
    $calheader = "<BR>
<table width='auto'>
    <tr><td><td align='center'>$editableicon</tr>
    <tr><td><a style='font-size: 14pt;' href='diary.php?date=$dateminusone$loggedinlink'><i class='fa fa-arrow-circle-left fa-lg' title='".date('l, F j, Y', strtotime($dateminusone))."'></i></a></td>
        <td style='font-size: 14pt; width: 300px'>$prettydate</td>
        <td><a style='font-size: 14pt;' href='diary.php?date=$dateplusone$loggedinlink'><i class='fa fa-arrow-circle-right fa-lg' title='".date('l, F j, Y', strtotime($dateplusone))."'></i></a></td></tr>
</table>
<BR><BR>";
    return $calheader;
}

function listfoodlink($diaryid, $serv, $servingsize, $unit, $brand, $product, $servings, $locked){
    //$editable = '<a href="javascript:void(0)" class="servingmodShow popuplink" id="servingmodShow" data-diaryid="'.$diaryid.'" data-serv="'.+$serv.'" data-servingsize="'.+$servingsize.'" data-servingsizeunit="'.$unit.'" >'.$brand.' '.$product.', '.+round($serv, 2).' '.$unit.'</a>';
    $text = '('.number_format(round($servings,1),1).') '.$brand.' '.$product.', '.+round($serv, 2).' '.$unit;
    $text = $locked ? $text : '<a href="javascript:void(0)" class="servingmodShow popuplink" id="servingmodShow" data-diaryid="'.$diaryid.'" data-serv="'.+$serv.'" data-servingsize="'.+$servingsize.'" data-servingsizeunit="'.$unit.'" >'.$text.'</a>';
    return $text;
}



function listmeals($date, $locked, $loggedin) {
    global $db;
    $sql = 'SELECT * FROM health.foodtime ORDER BY idfoodtime ASC';
    $meals = $db->fetcharray($sql, array(), '');
    $output = '';
    $output .= '<table><tr><th></th><th>Calories</th><th>Carbs</th><th>Fat</th><th>Protein</th><th>Sodium</th><th>Sugar</th><th>Chol</th></tr>'."\n";
    $output .= "<tr><th></th><th>(kcal)</th><th>(g)</th><th>(g)</th><th>(g)</th><th>(mg)</th><th>(g)</th><th>(mg)</th></tr>"."\n";
    foreach($meals as $key => $mealrow){
        $output .= '<tr><th>'.addicon($mealrow['meal'], $mealrow['idfoodtime'], $date, $locked or !$loggedin).'</th></tr>'."\n";
        $sql = "SELECT diaryid, brand, product, servings, unit, 
                    servingsize,
                    servings*servingsize as serv, 
                    servings*calories as cals, 
                    servings*totalcarbs as carbs, 
                    servings*totalfat as fat, 
                    servings*protein as prot, 
                    servings*sodium as sodium, 
                    servings*sugars as sugar,
                    servings*cholesterol as cholesterol,
                    servings
                FROM health.fooddiary fd 
                JOIN health.food f ON fd.foodid=f.foodid
                WHERE fd.meal=?
                 AND fd.date=?";
        $foods = $db->fetcharray($sql, array($mealrow['idfoodtime'], $date), 'ss');

        if (sizeof($foods) > 0) {
//<a href="diary.php?date='.$date.'&modify='.+$foodrow['diaryid'].'">'.$foodrow['brand'].' '.$foodrow['product'].', '.+round($foodrow['serv'], 2).' '.$foodrow['unit'].'</a></td>'
            foreach($foods as $key => $foodrow){
                $output .= '<tr><td align="left">'.listfoodlink($foodrow['diaryid'], $foodrow['serv'], $foodrow['servingsize'], $foodrow['unit'], $foodrow['brand'], $foodrow['product'], $foodrow['servings'], $locked or !$loggedin).'</td>'


                        . '<td>'.round($foodrow['cals']).'</td>'
                        . '<td>'.round($foodrow['carbs']).'</td>'
                        . '<td>'.round($foodrow['fat']).'</td>'
                        . '<td>'.round($foodrow['prot']).'</td>'
                        . '<td>'.round($foodrow['sodium']).'</td>'
                        . '<td>'.round($foodrow['sugar']).'</td>'
                        . '<td>'.round($foodrow['cholesterol']).'</td>'
                        . deleteicon($foodrow['diaryid'], $date, $locked or !$loggedin).'</tr>'."\n";
            }
            //TODO - implement food add and delete script
            $sql = "SELECT  
                    sum(servings*servingsize) as servsum, 
                    sum(servings*calories) as calsum, 
                    sum(servings*totalcarbs) as carbsum, 
                    sum(servings*totalfat) as fatsum, 
                    sum(servings*protein) as protsum, 
                    sum(servings*sodium) as sodiumsum, 
                    sum(servings*sugars) as sugarsum,
                    sum(servings*cholesterol) as cholsum
                FROM health.fooddiary fd 
                JOIN health.food f ON fd.foodid=f.foodid
                WHERE fd.meal=?
                 AND fd.date=?";
            $sumfoods = $db->fetcharray($sql, array($mealrow['idfoodtime'], $date), 'ss');
            $output .= '<tr><th>'.copyicon($mealrow['meal'], $date, !$loggedin).'</th>'
                    . '<th>'.round($sumfoods[0]['calsum']).'</th>'
                    . '<th>'.round($sumfoods[0]['carbsum']).'</th>'
                    . '<th>'.round($sumfoods[0]['fatsum']).'</th>'
                    . '<th>'.round($sumfoods[0]['protsum']).'</th>'
                    . '<th>'.round($sumfoods[0]['sodiumsum']).'</th>'
                    . '<th>'.round($sumfoods[0]['sugarsum']).'</th>'
                    . '<th>'.round($sumfoods[0]['cholsum']).'</th>'
                    . '</tr>'."\n";
            $output .= '<tr><td>&nbsp</td></tr>';

        }
        else{
            $output .=  '<tr><th></th></tr>'."\n";
            //. '<a href="?date='.$date.'&add='.$mealrow['idfoodtime'].'">Add Food</a></th></tr>'."\n";
            $output .=  '<tr><td>&nbsp</td></tr>';

        }
    }
    return $output;
}

function diarysummation($date){
    global $db;
    $output = '';
    $sql = "SELECT  sum(servings*servingsize) as servsum, 
                    sum(servings*calories) as calsum, 
                    sum(servings*totalcarbs) as carbsum, 
                    sum(servings*totalfat) as fatsum, 
                    sum(servings*protein) as protsum, 
                    sum(servings*sodium) as sodiumsum, 
                    sum(servings*sugars) as sugarsum,
                    sum(servings*cholesterol) as cholsum
                FROM health.fooddiary fd 
                JOIN health.food f ON fd.foodid=f.foodid
                WHERE fd.date=?";
            $summeals = $db->fetcharray($sql, array($date), 's');
    $output .= '<tr><td align="right">Totals</td>'
            . '<td>'.round($summeals[0]['calsum']).'</td>'
            . '<td>'.round($summeals[0]['carbsum']).'</td>'
            . '<td>'.round($summeals[0]['fatsum']).'</td>'
            . '<td>'.round($summeals[0]['protsum']).'</td>'
            . '<td>'.round($summeals[0]['sodiumsum']).'</td>'
            . '<td>'.round($summeals[0]['sugarsum']).'</td>'
            . '<td>'.round($summeals[0]['cholsum']).'</td>'
            . '</tr>'."\n";
    //TODO - get daily goals from DB, subtract exercise
    $user = getuserdata();
    $dailycals=+round($user['goalcals'],0);
    $dailycarbs=+round($user['goalcarbs'],0);
    $dailyfat=+round($user['goalfat'],0);
    $dailyprot=+round($user['goalprotein'],0);
    $dailysodium=+round($user['goalsodium'],0);
    $dailysugar=+round($user['goalsugar'],0);
    $dailychol=+round($user['goalcholesterol'],0);
    $output .= "<tr><td align=\"right\">Your Daily Goal</td>"
            . "<td>$dailycals</td>"
            . "<td>$dailycarbs</td>"
            . "<td>$dailyfat</td>"
            . "<td>$dailyprot</td>"
            . "<td>$dailysodium</td>"
            . "<td>$dailysugar</td>"
            . "<td>$dailychol</td>"
            . "</tr>\n";

    $remainingcals=round($dailycals-$summeals[0]['calsum']);
    $remainingcarbs=round($dailycarbs-$summeals[0]['carbsum']);
    $remainingfat=round($dailyfat-$summeals[0]['fatsum']);
    $remainingprot=round($dailyprot-$summeals[0]['protsum']);
    $remainingsodium=round($dailysodium-$summeals[0]['sodiumsum']);
    $remainingsugar=round($dailysugar-$summeals[0]['sugarsum']);
    $remainingchol=round($dailychol-$summeals[0]['cholsum']);
    
    //Color the remaining values red or green if they are negative or positive
    $remcalscolor = ($remainingcals>=0) ? 'green' : 'red';
    $remcarbscolor = ($remainingcarbs>=0) ? 'green' : 'red';
    $remfatcolor = ($remainingfat>=0) ? 'green' : 'red';
    $remprotcolor = ($remainingprot>=0) ? 'green' : 'red';
    $remsodiumcolor = ($remainingsodium>=0) ? 'green' : 'red';
    $remsugarcolor = ($remainingsugar>=0) ? 'green' : 'red';
    $remcholcolor = ($remainingchol>=0) ? 'green' : 'red';

    $output .= '<tr><td align="right">Remaining</td>'
            . '<td><font color="'.$remcalscolor.'"><b>'.+$remainingcals.'</b></font></td>'
            . '<td><font color="'.$remcarbscolor.'"><b>'.+$remainingcarbs.'</b></font></td>'
            . '<td><font color="'.$remfatcolor.'"><b>'.+$remainingfat.'</b></font></td>'
            . '<td><font color="'.$remprotcolor.'"><b>'.+$remainingprot.'</b></font></td>'
            . '<td><font color="'.$remsodiumcolor.'"><b>'.+$remainingsodium.'</b></font></td>'
            . '<td><font color="'.$remsugarcolor.'"><b>'.+$remainingsugar.'</b></font></td>'
            . '<td><font color="'.$remcholcolor.'"><b>'.+$remainingchol.'</b></font></td>'
            . '</tr>'."\n";
    $output .= "<tr><th></th><th>Calories\nkcal</th><th>Carbs</th><th>Fat</th><th>Protein</th><th>Sodium</th><th>Sugar</th><th>Chol</th></tr>"."\n";
    $output .= "<tr><th></th><th>(kcal)</th><th>(g)</th><th>(g)</th><th>(g)</th><th>(mg)</th><th>(g)</th><th>(mg)</th></tr>"."\n";
    $output .= '</table>'."\n";

    //TODO - If every day were like today... You'd way xxxlbs in 5 weeks
    //FIX - too many table headers, use bold style instead
    return $output;
}
function navbar(){
    $nav = '
<div class="container">
   <div class="row">
      <div class="col-md-12">
         <nav class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
               <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
               <span class="sr-only">Toggle navigation</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               </button>
               <a class="navbar-brand" href="#">Health</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
               <ul class="nav navbar-nav">
<li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>
        <li><a href="#">Link</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">One more separated link</a></li>
          </ul>
        </li>
               </ul>
               <!--<form class="navbar-form navbar-left" role="search">
                  <div class="form-group">
                     <input type="text" class="form-control" placeholder="Search">
                  </div>
                  <button type="submit" class="btn btn-default">Submit</button>
               </form>-->
               <ul class="nav navbar-nav navbar-right">
                  <li><a href="#">Link</a></li>
               </ul>
            </div>
            <!-- /.navbar-collapse -->
         </nav>
      </div>
   </div>
</div>        
    ';
    return $nav;

}
function pctaverager($value, $days, $norm){
    foreach ($value as $key=>$val){
        $valavgsum = 0;
        $period = $days>$key ? $key : $days-1;
        for ($x=0; $x<=$period; $x++){
            $valavgsum += $value[$key-$x]/($norm/100)/($period+1);
        }
        $valueavg[] = $value[$key] == null ? null : round($valavgsum,2);
        
        /*
        if ($key>$days and $value[$key]!=null){
            $valavgsum = 0;
            for($x=0; $x<$days; $x++){
                $valavgsum += $value[$key-$x]!=null ? $value[$key-$x]/($norm/100)/$days : 100/$days;
                //$valavgsum += $value[$key-$x]/($norm/100)/$days;
            }
            $valueavg[] = $valavgsum == null ? null : round($valavgsum,2);
        }
        else{
            //$valueavg[] = $value[$key]/($norm/100);
            $valueavg[] = null;
        }
         * 
         */
    }
    return $valueavg;
}
function averager($value, $days){
    foreach ($value as $key=>$val){
        $valavgsum = 0;
        $period = $days>$key ? $key : $days-1;
        for ($x=0; $x<=$period; $x++){
            $valavgsum += $value[$key-$x]/($period+1);
        }
        $valueavg[] = round($valavgsum,2);
    }
    return $valueavg;
}
function differential($value, $days){
    foreach ($value as $key=>$val){
        $period = $days>$key ? $key : $days-1;
        $week = 7>$key ? $key : 7;
        $valdiff = 0;
        for ($x=1; $x<=$period; $x++){
            $valdiff += $week*($value[$key-($x-1)] - $value[$key-($x-0)])/($period+1);
        }
        $valueavg[] = round($valdiff,2);
    }
    return $valueavg;
}
function find_closest($array, $date){
    //$count = 0;
    foreach($array as $day)
    {
        //$interval[$count] = abs(strtotime($date) - strtotime($day));
        $interval[] = abs($date - $day);
        //$count++;
    }

    asort($interval);
    $closest = key($interval);

    //echo $array[$closest];
    return $closest;
}
function getuserdata() {
    global $db, $cals_in_carbs, $cals_in_protein, $cals_in_fat;
    $sql = "SELECT * FROM health.user";
    $user = $db->fetcharray($sql, array(), '');
    
    $myuser['gender'] = ($user[0]['gender']=='male' or $myuser[0]['gender']=='female') ? $user[0]['gender'] : 'male';
    $myuser['initialweight'] = is_numeric($user[0]['initialweight']) ? $user[0]['initialweight'] : 300;
    $myuser['height'] = is_numeric($user[0]['height']) ? $user[0]['height'] : 70;
    $myuser['birthday'] = validateDate($user[0]['birthday']) ? $user[0]['birthday'] : '1980-01-01';
    $myuser['activity'] = is_numeric($user[0]['activity']) ? $user[0]['activity'] : 1.2;
    $myuser['goalcals'] = is_numeric($user[0]['goalcals']) ? $user[0]['goalcals'] : 2000;
    $myuser['macrocarbpct'] = is_numeric($user[0]['macrocarbpct']) ? $user[0]['macrocarbpct'] : 50;
    $myuser['macrofatpct'] = is_numeric($user[0]['macrofatpct']) ? $user[0]['macrofatpct'] : 30;
    $myuser['macroproteinpct'] = is_numeric($user[0]['macroproteinpct']) ? $user[0]['macroproteinpct'] : 20;
    $myuser['goalsodium'] = is_numeric($user[0]['goalsodium']) ? $user[0]['goalsodium'] : 2300;
    $myuser['goalsugar'] = is_numeric($user[0]['goalsugar']) ? $user[0]['goalsugar'] : 84;
    $myuser['goalfiber'] = is_numeric($user[0]['goalfiber']) ? $user[0]['goalfiber'] : 38;
    $myuser['goalpotassium'] = is_numeric($user[0]['goalpotassium']) ? $user[0]['goalpotassium'] : 3500;
    $myuser['goalcholesterol'] = is_numeric($user[0]['goalcholesterol']) ? $user[0]['goalcholesterol'] : 300;
    $myuser['goalcarbs'] = round(($myuser['macrocarbpct']/100)*$myuser['goalcals']/$cals_in_carbs,1);
    $myuser['goalfat'] = round(($myuser['macrofatpct']/100)*$myuser['goalcals']/$cals_in_fat,1);
    $myuser['goalprotein'] = round(($myuser['macroproteinpct']/100)*$myuser['goalcals']/$cals_in_protein,1);
    return $myuser;
}
function newfood() {
    global $foodid, $brand, $product, $servingsize, $unit, $calories, $totalfat, $saturatedfat, 
            $polyunsaturatedfat, $monounsaturatedfat, $transfat, $cholesterol, $sodium, $potassium, 
            $totalcarbs, $dietaryfiber, $sugars, $protein, $vitamina, $vitaminc, $calcium, $iron,
            $confirmed, $cat1, $cat2, $cost, $depricated;
    $inputtype = 'input type="text" size="5" maxlength="100"';
    $html = '';

    $html .= '<h3>Add Food To Database:</h3><br>'."\n";
    $html .= '<form action="" method="post" autocomplete="off" id="nutform" name="nutform">'."\n";
    $html .= '<div id="nutrition"><table>
  <tr><td colspan="1">Brand Name:<td colspan="3"><input type="text" class="initfocus" tabindex=1 autofocus="autofocus" autocomplete="off" onkeyup="autocomplet(\'brand\')" size="22" maxlength="100" name="brand" value="'.($brand).'"><ul id="food_list_brand"></ul>
  <tr><td colspan="1">Product Name:<td colspan="3"><input type="text" tabindex=2 autocomplete="off" onkeyup="autocomplet(\'product\')" size="22" maxlength="100" name="product" value="'.($product).'"><ul id="food_list_product"></ul>
  <tr><td>&nbsp
  <tr><td>Serving Size:<td><'.$inputtype.' tabindex=3 name="servingsize" value="">
      <td>Serving Unit:<td><'.$inputtype.' tabindex=4 name="unit" value="">
  <tr><td>Calories:<td><'.$inputtype.' tabindex=5 name="calories" value="">
      <td>Sodium:<td><'.$inputtype.' tabindex=12 name="sodium" value="">
  <tr><td>Total Fat:<td><'.$inputtype.' tabindex=6 name="totalfat" value="">
      <td>Potassium:<td><'.$inputtype.' tabindex=13 name="potassium" value="">
  <tr><td>Saturated Fat:<td><'.$inputtype.' tabindex=7 name="saturatedfat" value="">
      <td>Total Carbs:<td><'.$inputtype.' tabindex=14 name="totalcarbs" value="">
  <tr><td>Polyunsaturated Fat:<td><'.$inputtype.' tabindex=8 name="polyunsaturatedfat" value="">
      <td>Dietary Fiber:<td><'.$inputtype.' tabindex=15 name="dietaryfiber" value="">
  <tr><td>Monounsaturated Fat:<td><'.$inputtype.' tabindex=9 name="monounsaturatedfat" value="">
      <td>Sugars:<td><'.$inputtype.' tabindex=16 name="sugars" value="">
  <tr><td>Trans Fat:<td><'.$inputtype.' tabindex=10 name="transfat" value="">
      <td>Protein:<td><'.$inputtype.' tabindex=17 name="protein" value="">
  <tr><td>Cholesterol:<td><'.$inputtype.' tabindex=11 name="cholesterol" value="">
  <tr><td>Vitamin A (%):<td><'.$inputtype.' tabindex=18 name="vitamina" value="">
      <td>Calcium (%):<td><'.$inputtype.' tabindex=20 name="calcium" value="">
  <tr><td>Vitamin C (%):<td><'.$inputtype.' tabindex=19 name="vitaminc" value="">
      <td>Iron (%):<td><'.$inputtype.' tabindex=21 name="iron" value="">
  <tr><td>&nbsp
  <tr><td>Category 1:<td><'.$inputtype.' tabindex=22 name="cat1" value="">
      <td>Category 2:<td><'.$inputtype.' tabindex=23 name="cat2" value="">  
  <tr><td>Cost (per srv):<td><'.$inputtype.' tabindex=24 name="cost" value="">
  <tr><td>Confirmed:<td><'.$inputtype.' tabindex=25 name="confirmed" value="">
      <td>Depricated:<td><'.$inputtype.' tabindex=26 name="depricated" value="">  


  <tr><td>&nbsp
  <input type="hidden" name="foodid" value="'.($foodid).'">
 </table></div>';
    //echo '<input type="hidden" name="auuid" value="'.$auuid.'">'."\n";
    $html .= '<input type="submit" name="create_food" value="Create Food Item">'."\n";
    $html .= '<input type="submit" name="update_food" value="Update Food Item">'."\n";
    $html .= '<input type="submit" name="delete_food" value="Delete Food Item">'."\n";
    $html .= '<span align="right" class="popupclose" id="crudfoodClose">Cancel</span>';
    $html .= '</form>'."\n";
    
    //$html = '<h3>Add Food To Database: </h3>'."\n";
    return $html;
}    
function addstats($date){
    $html = '
<form id="addstatsform" action = "javascript:addstats()" >
  Weight: <input type="text" class="initfocus" id="addstatsweight" name="weight" size="10" autocomplete="off" autofocus="autofocus" value=""><br>
  <input type="hidden" id="addstatsuser" name="user" value="1">
  <input type="hidden" id="addstatsdate" name="date" value="'.$date.'">

</form><BR><BR>';
    $html .= '<button class="button" onclick="addstats()" >Update Stats</button>';
    $html .= '<span align="right" class="popupclose" id="statsClose"><button class="button" >Cancel</button></span>';
    return $html;
}
function servingmod(){
    $html = '
<form id="servingmodform">
  Servings: <input type="text" class="initfocus" id="changeservings" name="changeservings" size="10" autocomplete="off" autofocus="autofocus" value=""><br>
  <input type="hidden" id="diaryid" name="diaryid" value="">
  <div id="servingsize"></div>
</form><BR><BR>';
    $html .= '<button class="button" onclick="servingmod()" >Update Servings</button>';
    $html .= '<span align="right" class="popupclose" id="servingmodClose"><button class="button" >Cancel</button></span>';
    return $html;
}

function diaryheader() {
    $html='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Food Diary</title>
<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<!--<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>-->
<script type="text/javascript" src="js/jquery-ui.js"></script>

</head>
<body>'."\n";
    return $html;
}
    
    
function footer($date) {
    return  '
    <BR>
    <div id="crudfood" class="popup" tabindex="-1">'.newfood().'</div>
    <div id="stats" class="popup" tabindex="-1">'.addstats($date).'</div>
    <div id="servingmod" class="popup" tabindex="-1">'.servingmod().'</div>
    
    
    </body>
    </html>
    ';
}//<span id="statsShow" class="popuplink">Add Stats(test)</span><BR><BR><BR><BR>
//    <span id="crudfoodShow" class="popuplink">Manage Food Database</span><BR><BR><BR><BR>

function is_meal($meal){
    return true;
}

$blanktable =  
'Nutrition Facts'
       .'<table id="nutrition-facts"><colgroup>'
       .'<col class="col-1">'
       .'<col class="col-2">'
       .'<col class="col-1">'
       .'<col class="col-2">'
       .'</colgroup>'
       .'<tr><td class="col-1">Calories<td class="col-2"><td class="col-1">Sodium<td class="col-2">'
       .'<tr><td class="col-1">Total Fat<td class="col-2"><td class="col-1">Potassium<td class="col-2">'
       .'<tr><td class="col-1 sub">Saturated<td class="col-2"><td class="col-1">Total Carbs<td class="col-2">'
       .'<tr><td class="col-1 sub">Polyunsaturated<td class="col-2"><td class="col-1">Dietary Fiber<td class="col-2">'
       .'<tr><td class="col-1 sub">Monounsaturated<td class="col-2"><td class="col-1">Sugars<td class="col-2">'
       .'<tr><td class="col-1 sub">Trans<td class="col-2"><td class="col-1">Protein<td class="col-2">'
       .'<tr><td class="col-1">Cholesterol<td class="col-2">'
       .'<tr class="alt"><td class="col-1">Vitamin A<td class="col-2"><td class="col-1">Calcium<td class="col-2">'
       .'<tr><td class="col-1">Vitamin C<td class="col-2"><td class="col-1">Iron<td class="col-2">'
       .'</table>';


        
