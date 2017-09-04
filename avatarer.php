<?php
$ym = file_get_contents('init.yaml');
$ymArray = explode("\n", $ym);
$image_size = (int)str_replace("image_size: ", "", $ymArray[0]);
$input_dir = str_replace("input_dir: ", "", $ymArray[1]);
$output_dir = str_replace("output_dir: ", "", $ymArray[2]);
$input_dir = substr($input_dir, 0, strlen($input_dir)-1);
class ImageManipulator
{
    protected $width;
    protected $height;
    protected $image;
    public function __construct($file = null)
    {
        if (null !== $file) {
            if (is_file($file)) {
                $this->setImageFile($file);
            } else {
                $this->setImageString($file);
            }
        }
    }
    public function setImageFile($file)
    {
        if (!(is_readable($file) && is_file($file))) {
            throw new InvalidArgumentException("Image file $file is not readable");
        }

        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }

        list ($this->width, $this->height, $type) = getimagesize($file);

        switch ($type) {
            case IMAGETYPE_GIF  :
                $this->image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG :
                $this->image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG  :
                $this->image = imagecreatefrompng($file);
                break;
            default             :
                throw new InvalidArgumentException("Image type $type not supported");
        }

        return $this;
    }
    public function setImageString($data)
    {
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }

        if (!$this->image = imagecreatefromstring($data)) {
            throw new RuntimeException('Cannot create image from data string');
        }
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return $this;
    }
    public function resample($width, $height, $constrainProportions = true)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        if ($constrainProportions) {
            if ($this->height >= $this->width) {
                $width  = round($height / $this->height * $this->width);
            } else {
                $height = round($width / $this->width * $this->height);
            }
        }
        $temp = imagecreatetruecolor($width, $height);
        imagecopyresampled($temp, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        return $this->_replace($temp);
    }
    public function enlargeCanvas($width, $height, array $rgb = array(), $xpos = null, $ypos = null)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        
        $width = max($width, $this->width);
        $height = max($height, $this->height);
        
        $temp = imagecreatetruecolor($width, $height);
        if (count($rgb) == 3) {
            $bg = imagecolorallocate($temp, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($temp, 0, 0, $bg);
        }
        
        if (null === $xpos) {
            $xpos = round(($width - $this->width) / 2);
        }
        if (null === $ypos) {
            $ypos = round(($height - $this->height) / 2);
        }
        
        imagecopy($temp, $this->image, (int) $xpos, (int) $ypos, 0, 0, $this->width, $this->height);
        return $this->_replace($temp);
    }
    public function crop($x1, $y1 = 0, $x2 = 0, $y2 = 0)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        if (is_array($x1) && 4 == count($x1)) {
            list($x1, $y1, $x2, $y2) = $x1;
        }
        
        $x1 = max($x1, 0);
        $y1 = max($y1, 0);
        
        $x2 = min($x2, $this->width);
        $y2 = min($y2, $this->height);
        
        $width = $x2 - $x1;
        $height = $y2 - $y1;
        
        $temp = imagecreatetruecolor($width, $height);
        imagecopy($temp, $this->image, 0, 0, $x1, $y1, $width, $height);
        
        return $this->_replace($temp);
    }
    protected function _replace($res)
    {
        if (!is_resource($res)) {
            throw new UnexpectedValueException('Invalid resource');
        }
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
        $this->image = $res;
        $this->width = imagesx($res);
        $this->height = imagesy($res);
        return $this;
    }
    public function save($fileName, $type = IMAGETYPE_JPEG)
    {
        $dir = dirname($fileName);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException('Error creating directory ' . $dir);
            }
        }
        try {
            switch ($type) {
                case IMAGETYPE_GIF  :
                    if (!imagegif($this->image, $fileName)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_PNG  :
                    if (!imagepng($this->image, $fileName)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_JPEG :
                default             :
                    if (!imagejpeg($this->image, $fileName, 95)) {
                        throw new RuntimeException;
                    }
            }
        } catch (Exception $ex) {
            throw new RuntimeException('Error saving image file to ' . $fileName);
        }
    }
    public function getResource()
    {
        return $this->image;
    }

    public function getWidth()
    {
        return $this->width;
    }
    public function getHeight()
    {
        return $this->height;
    }
}
//lol
$i=0;
function listFolderFiles($dir) {
    global $image_size;
    global $output_dir;
    global $i;
    $ffs = scandir($dir);
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
                    $manipulator->save($output_dir."/".$i.".".strtolower($ext));
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
}
listFolderFiles($input_dir);
echo "OK!";
?>