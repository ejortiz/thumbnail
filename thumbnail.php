<?php
/*create thumbnail function*/

//this function will be used to create a thumbnail from the entered image
/**
 *$original_image: the original input image 
 * $full_path: the full path for saving the thumbnail
 * $url_path: the url path that will be stored in the MySQL table to display later
 * $target_width: max desired thumbnail width
 * $target height: max desired thumbnail height
 **/

function create_thumbnail($original_image, $full_path, $url_path, $target_width, $target_height)
{
	//$original_image should be $_FILES['profile_pic']['name'] 
	
	if (!$original_image)
	{
		 $result = "No image selected!";
	}
	
	/*process the image*/
	else
	{
		//get the details of the original image and store into variables using list 
		list($original_width, $original_height, $original_type) = getimagesize($original_image);
		
		//calculate the ratio
		if($original_width <= $target_width && $original_height)
		{
			$ratio = 1;
		}//fi
		
		else if($original_width > $original_height) 
		{
			$ratio = $target_width/$original_width;
		}//fi esle
		
		else
		{
			$ratio = $target_height/$original_height;	
		}//esle
		
		//strip the extension off of the image filename
		//create and array with regex of acceptable data types
		//and take out any spaces in the name
		$image_types = array('/\.gif$/','/\.jpg$','/\.jpeg$/','/\.png$/');
		$image_name = preg_replace($image_types, '', $original_image);
		$image_name =str_replace(' ', '_', $image_name);
		
####################################################################################################################
		
		//create an image resource for the original image
		switch($image_types)
		{
			case 1://if the image is 1 or gif, create a gif source
				$original_source = @ imagecreatefromgif($original_image);
				if(!$original_source)
				{
					$result = 'Cannot process GIF Files. Use JPEG or PNG';
				}
				break;
			
			case 2: //the image is a jpeg or jpg; create a jpg source
				$original_source = imagecreatefromjpeg($original_image);
				break;
			
			case 3: //the image is a png; create a png source
				$original_source = imagecreatefrompng($original_image);
				break;
				
			default:
				$original_source = NULL;
				$result = 'Cannot identify file type.';
		}//end of switch
	
####################################################################################################################
		
		
		/*Check to see if the image resource is okay*/
		if(!$original_source)
		{
			$result = 'Problem copying original image';
		}
		
		/*if it's okay calculate the dimensions of the thumbnail*/
		else 
		{
			$thumb_width = round($original_width * $ratio);
			$thumb_height = round($original_height * $ratio);
			
			$stamp = round($ratio); //use this to give each differently-sized thumbnails as different names
			
			//create image resource for thumbnail
			$thumb_source = imagecreatetruecolor($thumb_width, $thumb_height);
			
			//create the actual thumbnail
			imagecopyresampled($thumb_source, $original_source, 0, 0, 0, 0, $thumb_width, $thumb_height, $original_width, $original_height);

####################################################################################################################
			
			//save the image
			switch($type)
			{
				case 1: //if the image is a gif and possible to make a gif, then allow to make a gif. If not...
					if (function_exists('imagegif'))
					{
						$thumb_success = imagegif($thumb_source,$full_path.$image_name.$stamp.'_thb.gif');
						$thumb_name = $image_name.'_thb.gif';
						$thumb_name_url = $url_path.$image_name.$stamp.'_thb.gif';
						
					}
					
					//...make a jpeg of quality 50
					else 
					{
						$thumb_success = imagejpeg($thumb_source,$full_path.$image_name.$stamp.'_thb.jpg', 50);
						$thumb_name = $image_name.'_thb.jpg';
						$thumb_name_url = $url_path.$image_name.$stamp.'_thb.jpg';
						
					}
					break;
					
				case 2:
					$thumb_success = imagejpeg($thumb_source, $full_path.$image_name.$stamp.'_thb.jpg', 100);
					$thumb_name = $image_name.'_thb.jpg';
					$thumb_name_url = $url_path.$image_name.$stamp.'_thb.jpg';
					break;
					
				case 3: 
					$thumb_success = imagepng($thumb_source, $full_path.$image_name.$stamp.'_thb.png', 100);
					$thumb_name = $image_name.'_thb.png';
					$thumb_name_url = $url_path.$image_name.$stamp.'_thb.png';
					break;
			}//end switch
	
####################################################################################################################
		
			
			if ($thumb_success)
			{
				$result = "Thumbnail $thumb_name created.";
				
				
			}
			
			else
			{
				$result = 'Problem creating thumbnail.';
				
			}
			
			//remove image resources from memory because they take up too much space
			imagedestroy($original_source);
			imagedestroy($thumb_source);
			 
		}
		
	}//esle process image
	
	return $thumb_name_url;// if it's successful, this string will be be true
	
	
}

?>