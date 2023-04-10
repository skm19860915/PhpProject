<?php
include(__DIR__ . "/../includes/config.php");
include(__DIR__ . "/../includes/db_connect.php");
include(__DIR__ . "/../includes/upload.class.php");
include(__DIR__ . "/../includes/functions.php");

// Get the files that are older than the defined deletion days...
$photos_older = $dbh->prepare("SELECT id, created_at, url FROM file WHERE updated_at <= (CURRENT_DATE() - INTERVAL 30 DAY) AND is_deleted = 1");
$photos_older->execute();

if($photos_older->rowCount() > 0) {
	
	while($photo = $photos_older->fetch(PDO::FETCH_ASSOC)) {	
		
		$photo_id = $photo["id"];
		$photo_path = $photo["url"];
		
		echo "DELETING $photo_id FROM $photo_path<br>";
		
		unlink("../$photo_path");
		
		$del_photo = $dbh->prepare("DELETE FROM file WHERE id = :file_id");
		$del_photo->bindParam(":file_id", $photo_id);
		$del_photo->execute();		
				
	}
	
}
?>