<?php

echo "Creating ML images\n";

$ffs = scandir($dir);
$total_amt = count($ffs)-2;

$array_output = array();

for ($i=0; $i < $total_amt; $i++) { 
	// to loop for whole dataset
	$khe = imagecreatefrompng($ml_dir."/color_images/".$i.".png");

	for ($x=0; $x < $image_size; $x++) { 
		for ($y=0; $y < $image_size; $y++) { 

			$color = imagecolorat($khe, $x, $y);
			$r = ($color >> 16) & 0xFF;
			$g = ($color >> 8) & 0xFF;
			$b = $color & 0xFF;
			$average = ($r + $b + $g)/3;
			imagesetpixel($khe, $x, $y, imagecolorallocate($khe,$average,$average,$average));

			$unitary_average = $average/255;
			
			$image_data_array[$x][$y] = floor($unitary_average*100000)/100000;

			$array_output[$i] = array(
				"id"=>$i,
				"data"=>$image_data_array
			);
		}
	}

	imagepng($khe,$ml_dir."/images/".$i.".png");
	update_line("Progress: ".($i+1)."/".$total_amt);
}

file_put_contents($ml_dir."/json_data", json_encode($array_output));

print("\nOK! Created ML images\n");

?>