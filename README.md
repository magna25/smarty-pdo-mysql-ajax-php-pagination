smarty-pdo-mysql-ajax-php-pagination
====================================

This is a simple php pagination class which uses pdo or mysqli and also supports ajax. 

<b>Usage</b><br/>
The pagination class takes options as an array and returns the links and result from database which then you can use to echo the rows.<br/>
<code>
```php
include_once("pagination.php");
$items = 5;
$page = 1;

if(isset($_GET['pg']) && is_numeric($_GET['pg']) && $_GET['pg'] != 0 && $page = $_GET['pg']){
    $limit = " LIMIT ".(($page-1)*$items).",$items";
}
else{
  $limit = " LIMIT $items";
}	

$options = array
          (
          'db_type'=> 'mysql',
          'db' => $db,
          'sql' => 'select * from users where first_name = ? or first_name = ?',
          'params' => array('John', 'Max')
          )
$obj = new Pagination($options);
$obj->adjacent(4);
$obj->limit($items);
$obj->current($page);
$obj->target("test.php");
//obj->ajax(true);
//obj->counter(false);
//obj->showFirstLast(false);
//obj->ellipsis(false);
$result = $obj->result($limit);

while($row = $result->fetch_object()){
  echo $row->last_name."<br/>";
}
//for pdo
foreach($result as $row){
  echo $row['last_name'];
}

$obj->showLinks();
```
</code>
<b>Ajax Example</b>
<code>
```html
<div class="data"></div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
```
```javascript
ajax(null);
function ajax(p){
	$.ajax({
		url:'test.php',
		type:'GET',
		data:{pg:p},
		success:function(data){
		  //you can also append the links and data to different classes, to put the links where you want.
			//$('.data').html($(data).filter('#data'));
			//$('.links').html($(data).filter('#links'));
			$('.data').html(data)
			
		}
	})
}
$('body').on("click", '.data li:not(.active, .disabled)', function (e) {
  var page = $(this).attr('data-page');
  ajax(page);
});
```
</code>
