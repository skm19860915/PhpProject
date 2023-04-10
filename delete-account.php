<?php
include("includes/config.php");
include("includes/db_connect.php");
include("includes/functions.php");	

if($_SESSION) {
	
	if(TEST_MODE) {
		header("Location: logout.php");
	} else {
	
		$user_id = $_SESSION["USER_ID"];
		
		$stmt = $dbh->prepare("	DELETE FROM user
								WHERE id = :user_id
								");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();
		
		$photos_query = $dbh->prepare("SELECT * FROM photo WHERE user_id = :user_id ORDER BY id");
		$photos_query->bindParam(":user_id", $user_id);
		$photos_query->execute();	
		
		// Delete photo from the hosting
		while($photo = $photos_query->fetch(PDO::FETCH_ASSOC)) {
			
			$photo_url = $photo["url"];
			
			unlink($photo_url);
			
		}
		
		// Delete all photos of this user
		$stmt = $dbh->prepare("	DELETE FROM photo
								WHERE user_id = :user_id
								");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();
		
		header("Location: logout.php");
	
	}
	
}
?>