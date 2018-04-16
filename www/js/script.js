// autocomplet : this function will be executed every time we change the text
var myData;
var diaryservingsize = 1;
function setservingsize(){
    diaryservingsize = eval($('#nutrition input[name=servingsize]').val());
    $('#adddiaryform input[name=servnorm]').val(diaryservingsize);
    document.getElementById("nutritionfacts").innerHTML = buildNutritionTable(myData);
}
function autocomplet(type) {
	var min_length = 1; // min caracters to display the autocomplete
	
        var brand_keyword = $('#nutrition input[name=brand]').val();
        var product_keyword = $('#nutrition input[name=product]').val();
        var brand_product_keyword = $('#nutrition input[name=brand_product]').val();
        //var mykeyid_keyword = $('#food_id').val();
    if (type ==="brand" && brand_keyword.length >= min_length){
        $('#food_list_product').hide();
        $('#food_list_id').hide();
        clearbutbrand();
        $.ajax({
            url: 'ajax_refresh.php',
            type: 'POST',
            data: {brand_keyword:brand_keyword},
            success:function(data){
		$('#food_list_brand').show();
		$('#food_list_brand').html(data);
            }
	});
    }
    if (type ==="product" && product_keyword.length >= min_length ){
        clearbutproduct();
        $('#food_list_brand').hide();
        $('#food_list_id').hide();
        $.ajax({
            url: 'ajax_refresh.php',
            type: 'POST',
            data: {brand_keyword:brand_keyword, product_keyword:product_keyword},
            success:function(data){
		$('#food_list_product').show();
		$('#food_list_product').html(data);
            }
	});
    }
    if (type ==="brand_product" && brand_product_keyword.length >= min_length ){
        //clearbutproduct()
        //$('#food_list_brand').hide();
        //$('#food_list_id').hide();
        $.ajax({
            url: 'ajax_refresh.php',
            type: 'POST',
            data: {brand_product_keyword:brand_product_keyword},
            success:function(data){
		$('#food_list_brand_product').show();
		$('#food_list_brand_product').html(data);
            }
	});
    }
    if (type ==="id"){
        var id_keyword = $('#food_id').val();
        $.ajax({
            url: 'ajax_refresh.php',
            type: 'POST',
            data: {id_keyword:id_keyword},
            success:function(data){
		$('#food_list_id').show();
		$('#food_list_id').html(data);
            }
	});
    }
    else{
        $('#food_list_id').hide();
        $('#food_list_brand').hide();
        $('#food_list_product').hide();
    }

}

// set_item : this function will be executed when we select an item
function set_item(item, _id, type) {

    if (type ==="brand"){
        //$('#food_brand').val(item);
        showNutrition('');
        $('#nutrition input[name=brand]').val(item);
	$('#food_list_brand').hide();
        
    }
    else if (type ==="product"){
        //$('#nutrition input[name=product]').val(item);
        $('#food_list_product').hide();
        showFood(_id);
        showNutrition(_id);
    }
    else if (type ==="brand_product"){
        //set the text field to the selected item foodid
        $('#nutrition input[name=brand_product]').val(item);
        $('#nutrition input[name=foodid]').val(_id);
        //hide the item list drop down
        $('#food_list_brand_product').hide();
        //populate the nutrition table
        showFood(_id);
    }
    else{
        // change input value
	$('#food_id').val(item);
	// hide proposition list
	$('#food_list_id').hide();
        //show nutrition table
        showFood(_id);
    }
	
        
        
}

function clearbutbrand(){
        $('#nutrition input[name=foodid]').val('');

        $('#nutrition input[name=product]').val('');
        $('#nutrition input[name=servingsize]').val('');
        $('#nutrition input[name=unit]').val('');
        $('#nutrition input[name=calories]').val('');
        $('#nutrition input[name=totalfat]').val('');
        $('#nutrition input[name=saturatedfat]').val('');
        $('#nutrition input[name=polyunsaturatedfat]').val('');
        $('#nutrition input[name=monounsaturatedfat]').val('');
        $('#nutrition input[name=transfat]').val('');
        $('#nutrition input[name=cholesterol]').val('');
        $('#nutrition input[name=sodium]').val('');
        $('#nutrition input[name=potassium]').val('');
        $('#nutrition input[name=totalcarbs]').val('');
        $('#nutrition input[name=dietaryfiber]').val('');
        $('#nutrition input[name=sugars]').val('');
        $('#nutrition input[name=protein]').val('');
        $('#nutrition input[name=vitamina]').val('');
        $('#nutrition input[name=vitaminc]').val('');
        $('#nutrition input[name=calcium]').val('');
        $('#nutrition input[name=iron]').val('');
        $('#nutrition input[name=cat1]').val('');
        $('#nutrition input[name=cat2]').val('');
        $('#nutrition input[name=cost]').val('');
        $('#nutrition input[name=depricated]').val('');
        $('#nutrition input[name=confirmed]').val('');
}
function clearbutproduct(){
        $('#nutrition input[name=foodid]').val('');


        $('#nutrition input[name=servingsize]').val('');
        $('#nutrition input[name=unit]').val('');
        $('#nutrition input[name=calories]').val('');
        $('#nutrition input[name=totalfat]').val('');
        $('#nutrition input[name=saturatedfat]').val('');
        $('#nutrition input[name=polyunsaturatedfat]').val('');
        $('#nutrition input[name=monounsaturatedfat]').val('');
        $('#nutrition input[name=transfat]').val('');
        $('#nutrition input[name=cholesterol]').val('');
        $('#nutrition input[name=sodium]').val('');
        $('#nutrition input[name=potassium]').val('');
        $('#nutrition input[name=totalcarbs]').val('');
        $('#nutrition input[name=dietaryfiber]').val('');
        $('#nutrition input[name=sugars]').val('');
        $('#nutrition input[name=protein]').val('');
        $('#nutrition input[name=vitamina]').val('');
        $('#nutrition input[name=vitaminc]').val('');
        $('#nutrition input[name=calcium]').val('');
        $('#nutrition input[name=iron]').val('');
        $('#nutrition input[name=cat1]').val('');
        $('#nutrition input[name=cat2]').val('');
        $('#nutrition input[name=cost]').val('');
        $('#nutrition input[name=depricated]').val('');
        $('#nutrition input[name=confirmed]').val('');
}
function showNutrition(str){
    if (str === "") {
        $('#nutrition input[name=foodid]').val('');
        $('#nutrition input[name=brand]').val('');
        $('#nutrition input[name=product]').val('');
        $('#nutrition input[name=servingsize]').val('');
        $('#nutrition input[name=unit]').val('');
        $('#nutrition input[name=calories]').val('');
        $('#nutrition input[name=totalfat]').val('');
        $('#nutrition input[name=saturatedfat]').val('');
        $('#nutrition input[name=polyunsaturatedfat]').val('');
        $('#nutrition input[name=monounsaturatedfat]').val('');
        $('#nutrition input[name=transfat]').val('');
        $('#nutrition input[name=cholesterol]').val('');
        $('#nutrition input[name=sodium]').val('');
        $('#nutrition input[name=potassium]').val('');
        $('#nutrition input[name=totalcarbs]').val('');
        $('#nutrition input[name=dietaryfiber]').val('');
        $('#nutrition input[name=sugars]').val('');
        $('#nutrition input[name=protein]').val('');
        $('#nutrition input[name=vitamina]').val('');
        $('#nutrition input[name=vitaminc]').val('');
        $('#nutrition input[name=calcium]').val('');
        $('#nutrition input[name=iron]').val('');
        $('#nutrition input[name=cat1]').val('');
        $('#nutrition input[name=cat2]').val('');
        $('#nutrition input[name=cost]').val('');
        $('#nutrition input[name=depricated]').val('');
        $('#nutrition input[name=confirmed]').val('');
    } 
    else {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        if (window.XMLHttpRequest) {xmlhttp = new XMLHttpRequest(); } 
        // code for IE6, IE5
        else {xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                //document.getElementById("txtHint").innerHTML = this.responseText;
                myData=eval("(" + this.responseText + ")");
                $('#nutrition input[name=foodid]').val(myData['foodid']);
                $('#nutrition input[name=brand]').val(decodeHtml(myData['brand']));
                $('#nutrition input[name=product]').val(decodeHtml(myData['product']));
                $('#nutrition input[name=servingsize]').val(parseFloat(myData['servingsize']));
                $('#nutrition input[name=unit]').val(decodeHtml(myData['unit']));
                $('#nutrition input[name=calories]').val(parseFloat(myData['calories']));
                $('#nutrition input[name=totalfat]').val(parseFloat(myData['totalfat']));
                $('#nutrition input[name=saturatedfat]').val(parseFloat(myData['saturatedfat']));
                $('#nutrition input[name=polyunsaturatedfat]').val(parseFloat(myData['polyunsaturatedfat']));
                $('#nutrition input[name=monounsaturatedfat]').val(parseFloat(myData['monounsaturatedfat']));
                $('#nutrition input[name=transfat]').val(parseFloat(myData['transfat']));
                $('#nutrition input[name=cholesterol]').val(parseFloat(myData['cholesterol']));
                $('#nutrition input[name=sodium]').val(parseFloat(myData['sodium']));
                $('#nutrition input[name=potassium]').val(parseFloat(myData['potassium']));
                $('#nutrition input[name=totalcarbs]').val(parseFloat(myData['totalcarbs']));
                $('#nutrition input[name=dietaryfiber]').val(parseFloat(myData['dietaryfiber']));
                $('#nutrition input[name=sugars]').val(parseFloat(myData['sugars']));
                $('#nutrition input[name=protein]').val(parseFloat(myData['protein']));
                $('#nutrition input[name=vitamina]').val(parseFloat(myData['vitamina']));
                $('#nutrition input[name=vitaminc]').val(parseFloat(myData['vitaminc']));
                $('#nutrition input[name=calcium]').val(parseFloat(myData['calcium']));
                $('#nutrition input[name=iron]').val(parseFloat(myData['iron']));
                $('#nutrition input[name=cat1]').val(decodeHtml(myData['cat1']));
                $('#nutrition input[name=cat2]').val(decodeHtml(myData['cat2']));
                $('#nutrition input[name=cost]').val(parseFloat(myData['cost']));
                $('#nutrition input[name=confirmed]').val(parseFloat(myData['confirmed']));
                $('#nutrition input[name=depricated]').val(parseFloat(myData['depricated']));
            }
        };
        xmlhttp.open("GET","getfood.php?q="+str,true);
        xmlhttp.send();
    }

}
function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

function showFood(str) {
    if (str === "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } 
    else { 
        // code for IE7+, Firefox, Chrome, Opera, Safari
        if (window.XMLHttpRequest) {xmlhttp = new XMLHttpRequest(); } 
        // code for IE6, IE5
        else {xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                
                myData=eval("(" + this.responseText + ")");
                
                //alert(myData['calories']);
                //$('#nutrition input[name=product]').val(myData[0]);

                document.getElementById("nutritionfacts").innerHTML = buildNutritionTable(myData);
            }
        };
        xmlhttp.open("GET","getfood.php?q="+str,true);
        xmlhttp.send();
    }
}

function updateNutritionTable(myData){
    document.getElementById("nutritionfacts").innerHTML = buildNutritionTable(myData);
}

function buildNutritionTable(myData){
    var servingsize = parseFloat((diaryservingsize*myData['servingsize']).toFixed(2));
    var table =  '<div align="center">Nutrition Facts for serving size of '+servingsize+' '+myData['unit']+'</div>'
       +'<table id="nutrition-facts"><colgroup>'
       +'<col class="col-1">'
       +'<col class="col-2">'
       +'<col class="col-1">'
       +'<col class="col-2">'
       +'</colgroup>'
       +'<tr><td class="col-1">Calories<td class="col-2">'+parseFloat((diaryservingsize*myData['calories']).toFixed(2))+'<td class="col-1">Sodium<td class="col-2">'+parseFloat((diaryservingsize*myData['sodium']).toFixed(2))+' mg'
       +'<tr><td class="col-1">Total Fat<td class="col-2">'+parseFloat((diaryservingsize*myData['totalfat']).toFixed(2))+' g<td class="col-1">Potassium<td class="col-2">'+parseFloat((diaryservingsize*myData['potassium']).toFixed(2))+' mg'
       +'<tr><td class="col-1 sub">Saturated<td class="col-2">'+parseFloat((diaryservingsize*myData['saturatedfat']).toFixed(2))+' g<td class="col-1">Total Carbs<td class="col-2">'+parseFloat((diaryservingsize*myData['totalcarbs']).toFixed(2))+' g'
       +'<tr><td class="col-1 sub">Polyunsaturated<td class="col-2">'+parseFloat((diaryservingsize*myData['polyunsaturatedfat']).toFixed(2))+' g<td class="col-1">Dietary Fiber<td class="col-2">'+parseFloat((diaryservingsize*myData['dietaryfiber']).toFixed(2))+' g'
       +'<tr><td class="col-1 sub">Monounsaturated<td class="col-2">'+parseFloat((diaryservingsize*myData['monounsaturatedfat']).toFixed(2))+' g<td class="col-1">Sugars<td class="col-2">'+parseFloat((diaryservingsize*myData['sugars']).toFixed(2))+' g'
       +'<tr><td class="col-1 sub">Trans<td class="col-2">'+parseFloat((diaryservingsize*myData['transfat']).toFixed(2))+' g<td class="col-1">Protein<td class="col-2">'+parseFloat((diaryservingsize*myData['protein']).toFixed(2))+' g'
       +'<tr><td class="col-1">Cholesterol<td class="col-2">'+parseFloat((diaryservingsize*myData['cholesterol']).toFixed(2))+' mg'
       +'<tr class="alt"><td class="col-1">Vitamin A<td class="col-2">'+parseFloat((diaryservingsize*myData['vitamina']).toFixed(2))+'%<td class="col-1">Calcium<td class="col-2">'+parseFloat((diaryservingsize*myData['calcium']).toFixed(2))+'%'
       +'<tr><td class="col-1">Vitamin C<td class="col-2">'+parseFloat((diaryservingsize*myData['vitaminc']).toFixed(2))+'%<td class="col-1">Iron<td class="col-2">'+parseFloat((diaryservingsize*myData['iron']).toFixed(2))+'%'
               +'</table>';
    return table;
}

function clearForm(myFormElement) {
  var elements = myFormElement.elements;
  myFormElement.reset();
  for(i=0; i<elements.length; i++) {
  field_type = elements[i].type.toLowerCase();
  switch(field_type) {
    case "text":
    case "password":
    case "textarea":
    case "hidden":
        elements[i].value = "";
        break;

    case "radio":
    case "checkbox":
        if (elements[i].checked) {
            elements[i].checked = false;
        }
        break;

    case "select-one":
    case "select-multi":
        elements[i].selectedIndex = -1;
        break;

    default:
        break;
  }
  }
}

jQuery(function () {

    function change_diary_date(new_date) {
        var new_url = '/health/diary.php';
        new_url += (new_url.indexOf('?') === -1) ? '?' : '&';
        new_url += 'date=' + new_date;
        location = new_url;
    }

    
    $("#tsDte").datepicker({
      defaultDate: $("#tsDte").attr("value"),
      dateFormat: 'yy-mm-dd',
      monthNames: ["January","February","March","April","May","June","July","August","September","October","November","December"],
      dayNames: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
      dayNamesMin: ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
      changeYear: true,
      changeMonth: true,
      showOtherMonths: true,
      selectOtherMonths: true,
      showButtonPanel: true,
      showAnim: 'clip',
      onSelect:change_diary_date
    });

});//?

function tsDatePickerClick() {
    $("#tsDte").datepicker('show');
}

function addstats(){
    var user = $('#addstatsuser').val();
    var date = $('#addstatsdate').val();
    var weight = $('#addstatsweight').val();
    if (weight===''){
        alert('Enter weight');
    }
    else{
        $.ajax({
          url: 'diary.php',
          type: 'post',//dbaddstats
          data: 'dbaddstats=1&user='+user+'&date='+date+'&weight='+weight,
          success: function(output) {
              //alert('success, server says '+output);
                $("#stats").css("display","none");
                $("#addstats").removeClass("back"); 
          }, 
          error: function() {
                alert('Update failure');
          }
        });
    }
}

function servingmod() {
    var diaryid = $('#diaryid').val();
    var servings = eval($('#changeservings').val()); //TUFF

    if (servings===''){
        alert('Enter servings');
    }
    else if (diaryid===''){
        alert('No DiaryID set');
    }
    else{
        $.ajax({
          url: 'diary.php',
          type: 'post',
          data:
          {
              diarymodify: 1,
              diaryid: diaryid,
              servings: servings
          },
          success: function(output) {
                $("#servingmod").hide(); 
                window.location=window.location;
          }, 
          error: function(e) {
                console.log(e);
                alert('Update failure: ' + e.responseText);
          }
        });
    }
}

function crudfood() {
    var $form = $(this).closest('form');
    if ($form.find(':input[name=brand]').val()===''){
        alert('Brand required.');
    }
    else if ($form.find(':input[name=product]').val()===''){
        alert('Product required.');
    }
    else{
        var formdata = $form.serializeArray();
        formdata.push( { name: $(this).attr('name'), value: $(this).val() } );
        
        $.ajax({
          url: 'diary.php',
          type: 'post',
          data: formdata,
          
          success: function(output) {
                //$("#alert").hide(); 
                //window.location=window.location;
                console.log(output);
                $form.find(':input[name=foodid]').val(output.foodid);
                alert(output.message);
          }, 
          error: function(e) {
                console.log(e);
                alert('Failure: ' + e.responseText);
          }
        });
    }
    
    // stop event propogation
    return false;
}

function copymeals(meal){
    var user = $('#'+meal+'modmealsuser').val();
    var fromdate = $('#'+meal+'modmealsfromdate').val();
    var todate = $('#'+meal+'modmealstodate').val();
    var frommeal = $('#'+meal+'modmealsfrommeal').val();
    var tomeal = $('input[name="'+meal+'modmealstomeal"]:checked').val();
    if ((todate==='') || (tomeal==='')){
        alert('Enter date and meal to copy to');
    }
    else{
        $.ajax({
          url: 'diary.php',
          type: 'post',//dbaddstats
          data: 'dbcopymeal=1&user='+user+'&fromdate='+fromdate+'&todate='+todate+'&frommeal='+frommeal+'&tomeal='+tomeal, //todo
          success: function(output) {
              //alert('success, server says '+output);
                $("#"+frommeal+"mod").css("display","none");
                $("#"+frommeal+"modcontent").removeClass("back"); 
          }, 
          error: function() {
                alert('Meal copy failure'+user+' '+fromdate+' '+todate+' '+frommeal+' '+tomeal);
          }
        });
    }
}

$(document).ready(function(){
    $(".popup").draggable();
    $(".popuplink").click(function(e){
        e.preventDefault();
        var popupcontent = '#'+$(this).attr('id').replace('Show','');
        $(popupcontent).fadeIn(300); //,function(){$(this).focus();}
        $(popupcontent).find("input.initfocus").focus();
    });
    $('.popupclose').click(function() { $(".popup").fadeOut(300); });//$(this).fadeOut(300);

    $(document).mouseup(function (e){ //close popup on clickoutside of popup
        if (!$(".popup").is(e.target) //if the target of the click isn't the container...
            && $(".popup").has(e.target).length === 0) { // ... nor a descendant of the container
            $(".popup").fadeOut(300);
        }
    });
    //$("#crudfoodClose, #crudfoodShow").click(function(){ $("#crudfood").toggle(); });
    //$("#statsClose, #statsShow").click(function(){ $("#stats").toggle(); });
    //$("#servingmodClose, .servingsmodShow").click(function(){ $("#servingmod").toggle(); });
    //$("#BreakfastmodmealsClose, #BreakfastmodmealsShow").click(function(){ $("#Breakfastmodmeals").toggle(); });
    //$("#LunchmodmealsClose, #LunchmodmealsShow").click(function(){ $("#Lunchmod").toggle(); });
    //$("#DinnermodmealsClose, #DinnermodmealsShow").click(function(){ $("#Dinnermodmeals").toggle(); });
    //$("#SnacksmodmealsClose, #SnacksmodmealsShow").click(function(){ $("#Snacksmodmeals").toggle(); });

    $(".servingmodShow").click(function () {
        $('#changeservings').val(eval($(this).data("serv")));
        $('#diaryid').val($(this).data("diaryid"));
        $('#servingsize').text('of ' + $(this).data("servingsize") + ' ' + $(this).data("servingsizeunit"));
    });
    
    $('#crudfood :submit').click(crudfood);
    //$(".popup").focusout(function() {
    //    $('.popup').hide();
    //});
});
