<?php
$line_update_sets = "";
function update_line($string_to_out) {
    global $line_update_sets;
    echo "\033[".(strlen($line_update_sets))."D";
    $line_update_sets = $string_to_out;
    echo $line_update_sets;
}

// Inclusion of image manipulator
require "src/image_mani.php";
require "config.php";

if(isset($argv[1])){$dir = $argv[1];}
if(isset($argv[2])){$ml_dir = $argv[2];}
if(isset($argv[3])){$image_size = $argv[3];}

// check all files n stuff exist, else error
if(!isset($ml_dir)){die("No output directory specified\n");}
if(!isset($dir)){die("No input directory specified\n");}
if(!isset($image_size)){die("No output size specified\n");}

if(!file_exists($dir)){die("Specified input directory does not exist\n");}

if(!file_exists($ml_dir)){
    mkdir($ml_dir);
}
if(!file_exists($ml_dir."/color_images/")){
    mkdir($ml_dir."/color_images/");
}
if(!file_exists($ml_dir."/images")){
    mkdir($ml_dir."/images");
}

echo "Resizing images!\n";
//lol
$i=0;
$result = "";
$ffs = scandir($dir);
$total_amt = count($ffs)-2;
foreach($ffs as $ff){
    unset($ext);
    $ext = pathinfo($dir.'/'.$ff, PATHINFO_EXTENSION);
    //If it is an image
    if($ff !='.' and $ff !='..') {
         try {
            if($ext=="jpg" || $ext=="png" || $ext=="PNG" || $ext=="JPG" || $ext=="jpeg") {
                $manipulator = new ImageManipulator($dir."/".$ff);
                $width  = $manipulator->getWidth();
                $height = $manipulator->getHeight();
                $centreX = round($width / 2);
                $centreY = round($height / 2);
                if ($width > $height) {
                    $x1 = $centreX - $centreY; 
                    $y1 = $centreY - $centreY; 
                    $x2 = $centreX + $centreY; 
                    $y2 = $centreY + $centreY; 
                }
                if ($width < $height) {
                    $x1 = $centreX - $centreX; 
                    $y1 = $centreY - $centreX; 
                    $x2 = $centreX + $centreX; 
                    $y2 = $centreY + $centreX; 
                }
                if ($width == $height) {
                    $x1 = 0; 
                    $y1 = 0; 
                    $x2 = $width; 
                    $y2 = $height; 
                }
                $manipulator->crop($x1, $y1, $x2, $y2);
                $manipulator->resample($image_size, $image_size);
                $manipulator->save($ml_dir."/color_images/".$i.".png", IMAGETYPE_PNG);

                
                update_line("Progress: ".($i+1)."/".$total_amt);

                $i++;
            } else if(is_dir($dir.'/'.$ff)) {
                //Recurse function if directory
                listFolderFiles($dir.'/'.$ff);
            }
        }
        catch(Exception $e) {
          echo 'Message: ' .$e->getMessage();
        }
    }
}

echo "\nOK! Created color images.\n";

include "src/getcol.php";
?>