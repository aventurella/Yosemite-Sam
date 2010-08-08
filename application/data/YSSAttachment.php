<?php

class YSSAttachment extends YSSCouchObject
{
	public $label;
	public $path;
	public $content_type;
	public $content_length;
	
	protected $type = 'attachment';
	
	private $domain;
	private $file;
	private $remote = false;
	
	public static function attachmentWithRemoteFileInDomain($file, $domain)
	{
		$object   = new YSSAttachment();
		$s3       = YSSDatabase::connection(YSSDatabase::kS3);
		
		$object->domain = $domain;
		$object->path   = YSSUtils::storage_path_for_domain($object->domain).'/'.$file;
		
		$response       = $s3->getInfo($object->path);
		
		if($response)
		{
			$object->content_type   = $response['type'];
			$object->content_length = $response['size'];
			$object->remote = true;
		}
		else
		{
			$object = null;
		}
		
		return $object;
	}
	public static function attachmentWithLocalFileInDomain($file, $domain)
	{
		$object                 = new YSSAttachment();
		$object->file           = $file;
		$object->domain         = $domain;
		$object->content_length = filesize($file);
		
		$fileinfo               = finfo_open(FILEINFO_MIME_TYPE);
		$object->content_type   = finfo_file($fileinfo, $file);
		
		finfo_close($fileinfo);
		
		return $object;
	}
	
	public function contents()
	{
		if($this->remote)
		{
			$s3       = YSSDatabase::connection(YSSDatabase::kS3);
			
			// $response = $s3->getObjectStream($this->path);
			// for caching ^^^
			// see: http://framework.zend.com/manual/en/zend.service.amazon.s3.html
			
			$s3->registerStreamWrapper("s3");
			$fp = fopen('s3://'.$this->path, 'rb');
			fpassthru($fp);
		}
		else
		{
			$fp = fopen($this->file, 'rb');
			fpassthru($fp);
		}
	}
	
	public function save()
	{ 
		$isNew        = $this->_rev == null ? true : false;
		$id           = YSSUtils::transform_to_id($this->_id);
		$this->path   = 'http://yss.com/api/attachments/'.$id;
		$remote_path  = YSSUtils::storage_path_for_domain($this->domain).'/'.$id;
		
		if(parent::save() && $isNew)
		{
			/*$s3      = YSSDatabase::connection(YSSDatabase::kS3);
			$s3->putFile($this->file,
			             $remote_path,
			               array(Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $this->content_type,
				                 Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PRIVATE));
				*/
		}
	}
	
	// the couchdb lib expects "name", but YSS uses "label", so we will just help it along
	// if an attachments needs to be added to couchdb via the lib.
	// __get and __isset overloads needed for this to work
	public function __get($key)
	{
		if($key == 'name')
			return $this->label;
		else
			return null;
	}
	
	public function __isset($key) 
	{
		if($key == 'name')
			return isset($this->label);
		else
			return isset($this->{$key});
	}

}
?>