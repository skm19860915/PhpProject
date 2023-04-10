<?php
require_once("vendor/autoload.php");

$apiKey = "AIzaSyDkGP4XbpCDaAB-qFIqnJqNIqStWWA1IOU";

$vision = new \Vision\Vision(
    $apiKey, 
    [
        // See a list of all features in the table below
        // Feature, Limit
        new \Vision\Feature(\Vision\Feature::FACE_DETECTION, 100),
    ]
);

$imagePath = "img/slide2.jpg";
$response = $vision->request(
    // See a list of all image loaders in the table below
    new \Vision\Request\Image\LocalImage($imagePath)
);

$faces = $response->getFaceAnnotations();
foreach ($faces as $face) {
    foreach ($face->getBoundingPoly()->getVertices() as $vertex) {
        echo sprintf('Person at position X %f and Y %f', $vertex->getX(), $vertex->getY());
    }
}

?>