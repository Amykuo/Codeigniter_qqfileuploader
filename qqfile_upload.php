<?php
class qqfile_upload
{
	public function __construct()
	{
	}

	public function qqUploadFileXhr($path)
	{
		$input = fopen("php://input","r");
		$temp = tmpfile();
		$realSize = stream_copy_to_stream($input,$temp);
		fclose($input);
		$iSuccess = 1;
		//echo 'file_size'.$_SERVER["CONTENT_LENGTH"];
		if($realSize != $_SERVER["CONTENT_LENGTH"]){
			$this->result->code = 9999;
			$iSuccess = 0;
			$this->result->msg = 'DEFAULT';			
		}
		if($iSuccess){
			/*
			echo $realSize;
			echo "\n";
			echo ini_get('upload_max_filesize');
			echo "\n";
			echo $size = $realSize/1024/1024;
			echo "\n";
			*/
			$size = $realSize/1024/1024;
			//if($size>ini_get('upload_max_filesize')){
			if($size>2){
				$this->result->code = 9999;
				$iSuccess = 0;
				$this->result->msg = 'FILE_SIZE_EXCEED';
			}
		}
		if($iSuccess){
			if(!$this->check_file_format()){
				$this->result->code = 9999;
				$iSuccess = 0;
				$this->result->msg = 'WRONG_FILE_FORMAT';
			}
		}
		if($iSuccess){
			if(!$this->check_file_name()){
				$this->result->code = 9999;
				$iSuccess = 0;
				$this->result->msg = 'FILE_NAME_EXIST';
			}
		}
		if($iSuccess){
			$target = fopen($this->path.$this->file_name,"w");
			fseek($temp,0,SEEK_SET);
			stream_copy_to_stream($temp,$target);
			fclose($target);
			$this->result->code = 0;
			$this->result->msg = '';
			$this->result->file_size = $realSize;
			$this->result->file_name = $this->file_name;
		}
	}

	public function qqUploadedFileForm()
	{
		$iSuccess = 1;
		
		if(!$this->check_file_format()){
			$this->result->code = 9999;
			$iSuccess = 0;
			$this->result->msg = 'WRONG_FILE_FORMAT';			
		}
		
		if($iSuccess){
			if(!$this->check_file_name()){
				$this->result->code = 9999;
				$iSuccess = 0;
				$this->result->msg = 'FILE_NAME_EXIST';
			}
		}
		if($iSuccess){
			if($_FILES['qqfile']['error']==1){
				$this->result->code = 9999;
				$iSuccess = 0;
				$this->result->msg = 'FILE_SIZE_EXCEED';
			}
		}
		if($iSuccess){
			//print_r($_FILES['qqfile']);
			//echo $_FILES['qqfile']['file_size'];
			$size = $_FILES['qqfile']['size']/1024/1024;
			if($size>2){
				$this->result->code = 9999;
				$iSuccess = 0;
				$this->result->msg = 'FILE_SIZE_EXCEED';
			}
		}
		if($iSuccess){
			
			if(!move_uploaded_file($_FILES['qqfile']['tmp_name'],$this->path.$this->file_name)){
			
				$this->result->code = 9999;
				$this->result->msg = 'DEFAULT';
			}else{
				$this->result->code = CODE_SUCCESS;
				$this->result->msg = '';
				$this->result->file_size = $_FILES['qqfile']['size'];
				$this->result->file_name = $this->file_name;
			}
			
		}
	}

	public function check_file_format()
	{
		$pathinfo = pathinfo($this->file_name);
		$this->ext = @$pathinfo['extension'];
		$allow_file_type = array('jpg','jpeg','gif','png');
		if(!in_array(strtolower($this->ext),$allow_file_type)){
			return false;	
		}else
			return true;
	}

	public function check_file_name()
	{
		$filename = $this->path.$this->file_name;
		if(file_exists($filename)){
			$pathinfo = pathinfo($filename);
			$filename = $pathinfo['filename'];
			$ext = $this->ext;
			$newfilename = '';
			for($i=1;$i<100;$i++){
				if(!file_exists($this->path).$filename.$i.'.'.$ext){
					$newfilename = $filename.$i.'.'.$ext;
					break;
				}
			}
			if($newfilename == ''){
				return false;
			}else{
				$this->file_name = $newfilename;
				return true;
			}
		}else return true;
	}

	public function do_upload($path)
	{
		$this->result->code = 9999;
		$this->result->msg = 'DEFAULT';
		$this->result->file_size = 0;
		$this->file_name = '';
		$this->ext = '';
		$this->path = $path;
		$iSuccess = 1;
		
		if($iSuccess){
			if(isset($_REQUEST['qqfile'])){
				$this->file_name = $_REQUEST['qqfile'];
				$this->qqUploadFileXhr($path);
			}
			
			if(isset($_FILES['qqfile'])){
				$this->file_name = $_FILES['qqfile']['name'];
				$this->qqUploadedFileForm();				
			}
			
		}
		
		return $this->result;	
	}
}
?>
