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
		$object         = new YSSAttachment();
		$object->domain = $domain;
		
		if(AWS_S3_ENABLED)
		{
			$object->path   = YSSUtils::storage_path_for_domain($object->domain).'/'.$file;

			$s3       = YSSDatabase::connection(YSSDatabase::kS3);
			$response = $s3->getInfo($object->path);

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
		}
		else
		{
			$session  = YSSSession::sharedSession();
			$database = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
			
			// view labels can be more than 1 word long, which means translating - => / is going to fail 
			// with labels > 1 word. So we have to take a more measured approach:
			$data     = $database->document($file);
			
			if(isset($data['error']))
			{
				$object = null;
			}
			else
			{
				$object->_id            = $data['_id'];
				$object->path           = YSSApplication::basePath().'/resources/attachments/'.YSSUtils::storage_path_for_domain($object->domain).'/'.$object->uriId();
				$object->content_type   = $data['content_type'];
				$object->content_length = $data['content_length'];
				$object->file           = $object->path;
			}
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
		if($this->remote && AWS_S3_ENABLED)
		{
			// $response = $s3->getObjectStream($this->path);
			// for caching ^^^
			// see: http://framework.zend.com/manual/en/zend.service.amazon.s3.html
			
			$s3  = YSSDatabase::connection(YSSDatabase::kS3);
			$s3->registerStreamWrapper("s3");
			$fp = fopen('s3://'.$this->path, 'rb');
			fpassthru($fp);
		}
		else
		{
			if(is_file($this->file))
			{
				$fp = fopen($this->file, 'rb');
				fpassthru($fp);
			}
		}
	}
	
	private function uriId()
	{
		return strtr($this->_id, '/', ':');
		//return urlencode($this->_id);
		//$id           = 
	}
	
	public function save()
	{ 
		$ok           = false;
		$isNew        = $this->_rev == null ? true : false;
		$id           = $this->uriId();
		$this->path   = 'http://yss.com/api/attachments/'.urlencode($this->_id);
		
		$remote_path = AWS_S3_ENABLED ? YSSUtils::storage_path_for_domain($this->domain).'/'.$id : YSSApplication::basePath().'/resources/attachments/'.YSSUtils::storage_path_for_domain($this->domain).'/'.$id;
		//$remote_path  = YSSUtils::storage_path_for_domain($this->domain).'/'.$id;
		//$remote_path = YSSApplication::basePath().'/resources/attachments/'.YSSUtils::storage_path_for_domain($this->domain).'/'.$id;
		
		if(parent::save() && $isNew)
		{
			if(AWS_S3_ENABLED)
			{
				$s3      = YSSDatabase::connection(YSSDatabase::kS3);
				$s3->putFile($this->file,
				             $remote_path,
				               array(Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $this->content_type,
					                 Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PRIVATE));
			}
			else
			{
				if(is_uploaded_file($this->file))
					move_uploaded_file($this->file, $remote_path);
				else
					copy($this->file, $remote_path);
			}
			
			$ok = true;
		}
		
		return $ok;
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