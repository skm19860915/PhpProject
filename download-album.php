<?php
include("includes/config.php");
include("includes/functions.php");
include("includes/db_connect.php");
include("includes/upload.class.php");

function short_random($l = 8) {
    return substr(md5(uniqid(mt_rand(), true)), 0, $l);
}
	
if(!isset($_GET["path"]) || !isset($_GET["name"])) {
	header("Location: dashboard.php?action=forbidden");
	exit;
}	

if(!$_SESSION) {
	header("Location: sign-in.php");
	exit;
}

$album_path = htmlspecialchars($_GET["path"]);
$album_name = htmlspecialchars($_GET["name"]);

$user_id = $_SESSION["USER_ID"];
$email = $_SESSION["EMAIL"];

$get_file_infos = $dbh->prepare(" SELECT 
								   f.id AS file_id, 
								   f.short_id, 
								   f.title, 
								   f.unique_id,
								   f.url,
								   f.folder_path,
								   f.ext,
								   f.diskspace,
								   f.created_at,
								   f.is_picture,
								   f.status
								   FROM file f
								   WHERE 
								   f.user_id = :user_id
								   AND 
								   f.folder_path = :folder_path
								   AND 
								   f.is_deleted = 0
								   ORDER BY
								   created_at DESC
									");

$get_file_infos->bindParam(":user_id", $user_id);
$get_file_infos->bindParam(":folder_path", $album_path);
$get_file_infos->execute();
						                        

if($get_file_infos->rowCount() > 0) {
	
	$files = array();
	$folder_path = "";
	$files_to_zip = "";
	
	while($file_infos = $get_file_infos->fetch(PDO::FETCH_ASSOC)) {
		
		$file_url = $file_infos["url"];
		$folder_path = $file_infos["folder_path"];
		$files[] = $file_url;
		
		if (file_exists($file_url)) {
			$files_to_zip .= "\"$file_url\"" . " ";
		}
	}
	
	// Remove trailing slash
	$folder_path = rtrim($folder_path, '/');
	
	$rand_name = short_random(6);
	$dl_file_name = $rand_name . "_" . htmlspecialchars($album_name) . ".zip";
		
	$cmd = "zip downloads/$dl_file_name $files_to_zip";
	//print_r($cmd);
	//$cmd = "ls";
	//print_r($cmd);

	$output = array();
	
	exec("$cmd > /dev/null &");
	
	$download_url = "https://radtriads.com/downloads/$dl_file_name";
	
	// Prepare the email to be sent to the user
	$album_query = $dbh->prepare("INSERT INTO download_email SET created_at = NOW(), to_email = :to_email, download_url = :download_url");
	$album_query->bindParam(":to_email", $email);
	$album_query->bindParam(":download_url", $download_url);
	$album_query->execute();
	
	header("Location: dashboard.php?toast=download_soon_ready&path=$album_path");
	
		
		
} else {
	
	header("Location: dashboard.php?path=$album_path&action=no_files_download");
	exit;
	
}
?>