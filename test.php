<?php
$uploadedfile = "uploads/a31adcebaad9a9d3ce02d728568794eee581adf1/2020%20VARIOUS/Hope-your-day-rocks.jpg";
	
$img = new Imagick($uploadfile);
$img->stripImage();
$img->writeImage($uploadfile);