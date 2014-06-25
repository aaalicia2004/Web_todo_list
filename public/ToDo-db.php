<?php 
//---------1. Establish DB Connection----------
$dbc = new PDO('mysql:host=127.0.0.1;dbname=ToDo-db', 'alicia', 'password');
// Tell PDO to throw exceptions on error
$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//---------------------

require_once('filestore.php');
class InvalidInputException extends Exception{}

// Getting list files from Filestore object using a txt file.
// $listObject = new Filestore ('data/list.txt');
// $list = $listObject->read(); 

//REMOVE ITEM FROM TODO LIST--------
//use query() to select data from the database

// if (isset($_GET['id'])){ //only used when something is removed from the to do list
// 	unset($list[$_GET['id']]);
// 	$listObject->write($list);
// }

//POST ITEM TO THE TODO LIST---------
if(!empty($_POST)) {
	// TODO check if $_POST['ToDoItem'] isset
	if (isset($_POST['NewToDoItem'])) {
		$stmt = $dbc->prepare("INSERT INTO ToDoList (ToDoItem) VALUES (:ToDoItem)");
		$stmt->bindValue(':ToDoItem', $_POST['NewToDoItem'], PDO::PARAM_STR);
		$stmt->execute();
	}

	if (isset($_POST['remove'])) {
		$stmt = $dbc->prepare('DELETE FROM ToDoList WHERE id = :id');
		$stmt->bindValue(':id', $_POST['remove'], PDO::PARAM_INT);
		$stmt->execute();
	}
}

$count = $dbc->query('SELECT count(*) FROM ToDoList')->fetchColumn();

if(!empty($_GET)){
	$page = $_GET['page'];
} else {
	$page = 1;
}
$pageNext = $page + 1;
$pagePrev = $page - 1;
$limit = 10;
$offset = (($limit * $page) - $limit);

$numPages = ceil($count / $limit);

$stmt = $dbc->prepare('SELECT * FROM ToDoList LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$list = $stmt->fetchAll(PDO::FETCH_ASSOC);//gathered the information from the DB



//UPLOAD FILE TO THE LIST -----only gets used when a file is uploaded to the form method
if (count($_FILES) > 0 && $_FILES['UploadFile1']['error'] == 0 && $_FILES['UploadFile1']['type']== 'text/plain') {
  	// Set the destination directory for uploads
    $upload_dir = "/vagrant/sites/todo.dev/public/uploads/";
    // Grab the filename from the uploaded file by using basename
    $Uploadfilename = basename($_FILES['UploadFile1']['name']);
    // Create the saved filename using the file's original name and our upload directory
    $saved_filename = $upload_dir . $Uploadfilename;
    // Move the file from the temp location to our uploads directory
    move_uploaded_file($_FILES['UploadFile1']['tmp_name'], $saved_filename);
// Check if we saved a file
    // Refactor for when we need to save uploaded file contents to DB
    $newfile = new Filestore($saved_filename); //$newfile was created as the placeholder for the new array to be merged with my master array $todo
    $list_array = $newfile->read();
    // $list = array_merge($list, $anotherfile);//this is the merging of the the newfile with the master array
    // $listObject->write($list);
    foreach ($list_array as $item) {
    	$stmt = $dbc->prepare("INSERT INTO ToDoList (ToDoItem) VALUES (:ToDoItem)");
		$stmt->bindValue(':ToDoItem', $item, PDO::PARAM_STR);
		$stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>To-Do</title>
	<link rel="stylesheet" href="ToDoStyleSheet.css" type="text/css">
</head>
<body>
	<h1>To-Do List</h1>
<? if (isset($saved_filename)): ?>
   <?= "<p>You can download your file <a href='/uploads/{$saved_filename}'>here</a></p>"; ?>
<? endif; ?>

<ul>
<? foreach ($list as $key => $item): ?>
	<li><?= htmlspecialchars(strip_tags($item['ToDoItem'])); ?> <button class="btn-remove" data-todo="<?= $item['id']; ?>">Remove</button></li> 
<? endforeach; ?>
</ul>
<? if($page > 1):?>
<?= "<a href='?page=$pagePrev'>Previous</a>";?>
<? endif; ?>
<? if($page < $numPages) : ?>
<?= "<a href='?page=$pageNext'>Next</a>";?>
<? endif; ?>

<form id="removeForm" action="todo-db.php" method="post">
    <input id="removeId" type="hidden" name="remove" value="">
</form>

<form method="POST"> 
	<p>
	    <label for="NewToDoItem">Add New Item</label>
	    <input id="NewToDoItem" name="NewToDoItem" type="text" autofocus>
	</p>
	<p>
	    <input type="Submit" Value="Add">
	</p>
</form>

<h1>Upload File</h1>

<form method="POST" enctype="multipart/form-data">
	<p>
		<label for="UploadFile1">File to upload:</label>
		<input id="UploadFile1" name="UploadFile1" type="file">
	</p>
	<p>
		<input type="Submit" value="Upload File">
	</p>
</form>

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script>
// removes the item from the DB
$('.btn-remove').click(function () {
    var todoId = $(this).data('todo');
    if (confirm('Are you sure you want to remove item ' + todoId + '?')) {
        $('#removeId').val(todoId);
        $('#removeForm').submit();
    };
});

</script>
</body>
</html>