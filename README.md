smarty-pdo-mysql-ajax-php-pagination
====================================

This is a simple php pagination class which uses pdo or mysqli and also supports ajax. It does all the work required including running the queries and returns the result along with the links. You can either specify pdo/mysqli as your db. Since it uses prepared statements, there won't be any concern regarding sql injections. The class is fairly simple and highly customizable according to your needs.

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

<b>Options</b><br/>

| Method        | Type, default | Definition                                                                     |
|---------------|:-------------:|--------------------------------------------------------------------------------|
| adjacent      |     int, 2    | Defines the max number of links displayed on each side of the the current link |
| limit         |    int, 10    | The maximum number of rows to be fetched per page                              |
| current       |     int, 1    | Current page                                                                   |
| target        | string, null  | The page you want to use the pagination on, ex.. test.php                      |
| ajax          | bool, false   | Defines wether to use ajax or not.                                             |
| counter       | bool, true    | Defines whether to show the counter or not                                     |
| showFirstLast | bool, true    | Defines whether to show the first last links or not                            |
| prevL         | string, <     | The previous link text/icon                                                    |
| nextL         | string, >     | The next link text/icon                                                        |
| firstL        | string, «     | The first link text/icon                                                       |
| lastL         | string, »     | The last link text/icon                                                        |
| ellipsis      | bool, true    | Defines whether to show ellipsis or not                                        |
| pClass        | string, pagination    | The css class for the UL element                                          |
