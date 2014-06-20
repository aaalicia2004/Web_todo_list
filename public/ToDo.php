<?php 
require_once('filestore.php');
//print_r($_GET);
//print_r($_POST);
// $todo = []; //set empty array to hold inputted items
$listObject = new Filestore ('data/list.txt');
$list = $listObject->read(); //$listObject->filename);

if (isset($_GET['id'])){ //only used when something is removed from the to do list
	unset($list[$_GET['id']]);
	$listObject->write($list);
	// header("Location: /ToDo.php");
	// exit (0);
}
if (isset($_POST['NewToDoItem'])){ //only used when something is posted in the add a new to do item
	$item = trim($_POST['NewToDoItem']);
	
	if (strlen($item) == 0 || strlen($item) > 240){
		throw new Exception('Invalid entry! Please make input greater than 0 characters and less than 240 characters!');
		}
	array_push($list, $_POST['NewToDoItem']);
	$listObject->write($list);
}
// Verify there were uploaded files and no errors
//var_dump($_FILES);

//only gets used when a file is uploaded to the form method
if (count($_FILES) > 0 && $_FILES['UploadFile1']['error'] == 0 && $_FILES['UploadFile1']['type']== 'text/plain'){
  	// Set the destination directory for uploads
    $upload_dir = "/vagrant/sites/todo.dev/public/uploads/";
    // Grab the filename from the uploaded file by using basename
    $Uploadfilename = basename($_FILES['UploadFile1']['name']);
    // Create the saved filename using the file's original name and our upload directory
    $saved_filename = $upload_dir . $Uploadfilename;
    // Move the file from the temp location to our uploads directory
    move_uploaded_file($_FILES['UploadFile1']['tmp_name'], $saved_filename);
// Check if we saved a file
    $newfile = new Filestore($saved_filename); //$newfile was created as the placeholder for the new array to be merged with my master array $todo
    $anotherfile = $newfile->read();
    $list = array_merge($list, $anotherfile);//this is the merging of the the newfile with the master array
    $listObject->write($list);
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
	<li><?= htmlspecialchars(strip_tags($item)) . "<a href = '?id=$key'> Mark Complete</a>";?></li> 
<? endforeach; ?>
</ul>

<form method="POST" action="/ToDo.php"> 
	<p>
	    <label for="NewToDoItem">Add New Item</label>
	    <input id="NewToDoItem" name="NewToDoItem" type="text">
	</p>
	<p>
	    <input type="Submit" Value="Add">
	</p>
</form>

<h1>Upload File</h1>

<form method="POST" enctype="multipart/form-data" action="/ToDo.php">
	<p>
		<label for="UploadFile1">File to upload:</label>
		<input id="UploadFile1" name="UploadFile1" type="file">
	</p>
	<p>
		<input type="Submit" value="Upload File">
	</p>
</form>
</body>
</html>