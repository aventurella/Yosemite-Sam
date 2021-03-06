<?php
require '../system/YSSEnvironmentServices.php';

require YSSApplication::basePath().'/application/libs/axismundi/data/AMQuery.php';
require YSSApplication::basePath().'/application/libs/axismundi/display/AMDisplayObject.php';
require YSSApplication::basePath().'/application/libs/axismundi/forms/AMForm.php';
require YSSApplication::basePath().'/application/libs/axismundi/forms/validators/AMPatternValidator.php';
require YSSApplication::basePath().'/application/libs/axismundi/forms/validators/AMInputValidator.php';
require YSSApplication::basePath().'/application/libs/axismundi/forms/validators/AMErrorValidator.php';
require YSSApplication::basePath().'/application/libs/axismundi/services/AMServiceManager.php';

require YSSApplication::basePath().'/application/system/YSSService.php';
require YSSApplication::basePath().'/application/system/YSSSecurity.php';
require YSSApplication::basePath().'/application/data/YSSState.php';
require YSSApplication::basePath().'/application/data/YSSAnnotation.php';
require YSSApplication::basePath().'/application/data/YSSTask.php';
require YSSApplication::basePath().'/application/data/YSSNote.php';




class YSSServiceAnnotations extends YSSService
{
	protected $requiresAuthorization = true;
	
	public function registerServiceEndpoints($method)
	{
		switch($method)
		{
			case "GET":
				$this->addEndpoint("GET",    "/api/project/{project_id}/{view_id}/{state_id}/tasks",               "getTasks");
				$this->addEndpoint("GET",    "/api/project/{project_id}/{view_id}/{state_id}/notes",               "getNotes");
				$this->addEndpoint("GET",    "/api/project/{project_id}/{view_id}/{state_id}/annotations",         "getAnnotations");
				break;
				
			case "POST":
				$this->addEndpoint("POST",    "/api/project/{project_id}/{view_id}/{state_id}/annotations",     "createAnnotation");
				$this->addEndpoint("POST",    "/api/project/{project_id}/{view_id}/{state_id}/{annotation_id}", "updateAnnotation");
				break;
			
			case "DELETE":
				$this->addEndpoint("DELETE", "/api/project/{project_id}/{view_id}/{state_id}/{annotation_id}",  "deleteAnnotation");
				break;
		}
	}
	
	public function getTasks($project_id, $view_id, $state_id)
	{
		$project_id = YSSUtils::transform_to_id($project_id);
		$view_id    = YSSUtils::transform_to_id($view_id);
		$state_id   = YSSUtils::transform_to_id($state_id);
		
		$session  = YSSSession::sharedSession();
		$options = array('key'            => 'project/'.$project_id.'/'.$view_id.'/'.$state_id, 
		                 'include_docs'   => true);
		
		$database = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
		// use the view not the list of you want JSON instead of HTML
//		echo $database->formatList("project/annotation-renderer", "task-report", $options, true);
		echo $database->formatList("project/annotation-aggregate", "task-report", $options, true);
	}
	
	public function getNotes($project_id, $view_id, $state_id)
	{
		$project_id = YSSUtils::transform_to_id($project_id);
		$view_id    = YSSUtils::transform_to_id($view_id);
		$state_id   = YSSUtils::transform_to_id($state_id);
		
		$session  = YSSSession::sharedSession();
		$options = array('key'            => 'project/'.$project_id.'/'.$view_id.'/'.$state_id, 
		                 'include_docs'   => true);
		
		$database = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
		// use the view not the list of you want JSON instead of HTML
//		echo $database->formatList("project/annotation-renderer", "note-report", $options, true);
		echo $database->formatList("project/annotation-aggregate", "note-report", $options, true);
	}
	
	public function getAnnotations($project_id, $view_id, $state_id)
	{
		$project_id = YSSUtils::transform_to_id($project_id);
		$view_id    = YSSUtils::transform_to_id($view_id);
		$state_id   = YSSUtils::transform_to_id($state_id);
		
		$session  = YSSSession::sharedSession();
		$options  = array('key'            => 'project/'.$project_id.'/'.$view_id.'/'.$state_id, 
		                  'include_docs'   => true);
		
		$database = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
		// use the view not the list of you want JSON instead of HTML
//		echo $database->formatList("project/annotation-renderer", "annotations-report", $options, true);
		echo $database->formatList("project/annotation-aggregate", "annotations-report", $options, true);
	}
	
	private function applyBaseAnnotationValidators(&$input)
	{
		$input->addValidator(new AMPatternValidator('project_id', AMValidator::kRequired, '/^[a-z\d-]{2,}$/', "Invalid project id. Expecting minimum 2 lowercase characters."));
		$input->addValidator(new AMPatternValidator('view_id', AMValidator::kRequired, '/^[a-z\d-]{2,}$/', "Invalid view id. Expecting minimum 2 lowercase characters."));
		$input->addValidator(new AMPatternValidator('state_id', AMValidator::kRequired, '/^[a-z\d-]{2,}$/', "Invalid state id. Expecting minimum 2 lowercase characters."));
	}
	
	private function applyPostValidators(&$input)
	{
		$input->addValidator(new AMPatternValidator('annotation_id', AMValidator::kRequired, '/^[a-z0-9]{32}$/', "Invalid annotation id."));
		$input->addValidator(new AMInputValidator('label', AMValidator::kOptional, 2, null, "Invalid label.  Expecting minimum 2 characters."));
		$input->addValidator(new AMInputValidator('description', AMValidator::kOptional, 2, null, "Invalid description.  Expecting minimum 2 characters."));
		$input->addValidator(new AMPatternValidator('context', AMValidator::kOptional, '/^[\w\d -]+$/', "Invalid context"));
		$input->addValidator(new AMPatternValidator('x', AMValidator::kOptional, '/^((\d+)\.(\d)+))$/', "Invalid x coordinate"));
		$input->addValidator(new AMPatternValidator('y', AMValidator::kOptional, '/^((\d+)\.(\d)+))$/', "Invalid y coordinate"));
		$input->addValidator(new AMPatternValidator('width', AMValidator::kRequired, '/^[\d]+$/', "Invalid width coordinate"));
		$input->addValidator(new AMPatternValidator('height', AMValidator::kRequired, '/^[\d]+$/', "Invalid height coordinate"));
	}
	
	private function applyPutValidators(&$input)
	{
		$input->addValidator(new AMInputValidator('label', AMValidator::kRequired, 2, null, "Invalid description.  Expecting minimum 2 characters."));
		$input->addValidator(new AMInputValidator('description', AMValidator::kOptional, 2, null, "Invalid description.  Expecting minimum 2 characters."));
		$input->addValidator(new AMPatternValidator('x', AMValidator::kRequired, '/^((\d+)\.(\d)+))$/', "Invalid x coordinate"));
		$input->addValidator(new AMPatternValidator('y', AMValidator::kRequired, '/^((\d+)\.(\d)+))$/', "Invalid y coordinate"));
		$input->addValidator(new AMPatternValidator('width', AMValidator::kRequired, '/^[\d]+$/', "Invalid width coordinate"));
		$input->addValidator(new AMPatternValidator('height', AMValidator::kRequired, '/^[\d]+$/', "Invalid height coordinate"));
		$input->addValidator(new AMPatternValidator('type', AMValidator::kRequired, '/^task|note$/', "Invalid annotation type. Expecting task or note"));
		$input->addValidator(new AMPatternValidator('context', AMValidator::kOptional, '/^[\w\d -]+$/', "Invalid context"));
	}
	
	private function applyPutTaskValidators(&$input)
	{
		$input->addValidator(new AMPatternValidator('assigned_to', AMValidator::kOptional, '/^[\w\d -]{2,}$/', "Invalid asignee"));
		$input->addValidator(new AMPatternValidator('status', AMValidator::kOptional, '/^[01]$/', "Invalid status.  Expecting 0 or 1"));
		$input->addValidator(new AMPatternValidator('priority', AMValidator::kOptional, '/^[0-9]$/', "Invalid priority.  Expecting 0-9"));
		//$input->addValidator(new AMPatternValidator('group', AMValidator::kOptional, '/^[01]$/', "Invalid status.  Expecting 0 or 1"));
		//$input->addValidator(new AMPatternValidator('estimate', AMValidator::kOptional, '/^[0-9]$/', "Invalid priority.  Expecting 0-9"));
	}
	
	private function applyPostTaskValidators(&$input)
	{
		$input->addValidator(new AMPatternValidator('assigned_to', AMValidator::kOptional, '/^[\w\d -]{2,}$/', "Invalid asignee"));
		$input->addValidator(new AMPatternValidator('status', AMValidator::kOptional, '/^[01]$/', "Invalid status.  Expecting 0 or 1"));
		$input->addValidator(new AMPatternValidator('priority', AMValidator::kOptional, '/^[0-9]$/', "Invalid priority.  Expecting 0-9"));
		//$input->addValidator(new AMPatternValidator('group', AMValidator::kOptional, '/^[01]$/', "Invalid status.  Expecting 0 or 1"));
		//$input->addValidator(new AMPatternValidator('estimate', AMValidator::kOptional, '/^[0-9]$/', "Invalid priority.  Expecting 0-9"));
	}
	
	public function createAnnotation($project_id, $view_id, $state_id)
	{
		$response     = new stdClass();
		$response->ok = false;
		
		$project_id = YSSUtils::transform_to_id($project_id);
		$view_id    = YSSUtils::transform_to_id($view_id);
		$state_id   = YSSUtils::transform_to_id($state_id);
		
		$data                  = $_POST;
		$data['view_id']       = $view_id;
		$data['project_id']    = $project_id;
		$data['state_id']      = $state_id;
		
		$context = array(AMForm::kDataKey=>$data);
		$input   = AMForm::formWithContext($context);
		
		$this->applyBaseAnnotationValidators($input);
		
		if($input->isValid)
		{
			$state = YSSState::stateWithId('project/'.$project_id.'/'.$view_id.'/'.$state_id);
			
			if($state)
			{
				$this->applyPutValidators($input);
				
				if($input->isValid)
				{
					$annotation = null;
				
					if($input->type == 'task') 
					{
						$this->applyPutTaskValidators($input);
					
						if(!$input->isValid)
						{
							$input->addValidator(new AMErrorValidator('error', 'Invalid Task') );
							$this->hydrateErrors($input, $response);
							echo json_encode($response);
							exit;
						}
					
						$annotation = new YSSTask();
						
						if($input->status)
							$annotation->status = $input->status;
							
						if($input->assigned_to)
							$annotation->assigned_to = $input->assigned_to;
								
						if($input->priority)
							$annotation->priority = $input->priority;
						
						if($input->estimate)
							$annotation->estimate = $input->estimate;
					}
				
					if(!$annotation) $annotation = new YSSNote();
				
					if($input->context)
						$annotation->context = $input->context;
						
					
					$annotation->label = $input->label;
					$annotation->description = $input->description;
					$annotation->x = $input->x;
					$annotation->y = $input->y;
					$annotation->width = $input->width;
					$annotation->height = $input->height;
					
					if($state->addAnnotation($annotation))
					{
						$response->ok = true;
						$response->id = $annotation->_id;
					}
					else
					{
						$input->addValidator(new AMErrorValidator('error', 'Unable to save annotation') );
						$this->hydrateErrors($input, $response);
					}
				}
				else
				{
					$input->addValidator(new AMErrorValidator('error', 'Unable to save annotation') );
					$this->hydrateErrors($input, $response);
				}
			}
			else
			{
				$input->addValidator(new AMErrorValidator('error', 'Invalid state') );
				$this->hydrateErrors($input, $response);
			}
		}
		
		echo json_encode($response);
	}
	
	
	public function updateAnnotation($project_id, $view_id, $state_id, $annotation_id)
	{
		$response     = new stdClass();
		$response->ok = false;
		
		$project_id = YSSUtils::transform_to_id($project_id);
		$view_id    = YSSUtils::transform_to_id($view_id);
		$state_id   = YSSUtils::transform_to_id($state_id);
		
		$data                  = $_POST;
		$data['view_id']       = $view_id;
		$data['project_id']    = $project_id;
		$data['state_id']      = $state_id;
		$data['annotation_id'] = $annotation_id;
		
		$context = array(AMForm::kDataKey=>$data);
		$input   = AMForm::formWithContext($context);
		
		$this->applyBaseAnnotationValidators($input);
		
		if($input->isValid)
		{
			$this->applyPostValidators($input);
			
			if($input->isValid)
			{
				$session  = YSSSession::sharedSession();
				$database = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
				
				// annotations can be of type task or note
				// so get the array first, and then hydrate an object accordingly.
				$raw_annotation = $database->document('project/'.$project_id.'/'.$view_id.'/'.$state_id.'/'.$annotation_id);
				
				if($raw_annotation && !isset($raw_annotation['error']))
				{
					$type       = $raw_annotation['type'];
					switch($type)
					{
						case 'task':
							$this->updateTask($raw_annotation, $input, $response);
							break;
						
						case 'note':
							$this->updateNote($raw_annotation, $input, $response);
							break;
					}
				}
			}
		}
		
		echo json_encode($response);
	}
	
	private function updateTask(&$array, &$input, &$response)
	{
		$this->applyPostTaskValidators($input);
		
		if($input->isValid)
		{
			$dirty = false;
			$annotation       = YSSTask::taskWithArray($array);
			$initial_checksum = md5($annotation->__toString());
			$final_checksum   = null;
			
			$this->updateBaseAnnotation($annotation, $input, $response);
			
			if($input->assigned_to && $annotation->assigned_to != $input->assigned_to)
				$annotation->assigned_to = $input->assigned_to;
		
			if($input->status && $annotation->status != $input->status)
				$annotation->status = $input->status;
			
			if($input->priority && $annotation->priority != $input->priority)
				$annotation->priority = $input->priority;
			
			if($input->estimate && $annotation->estimate != $input->estimate)
				$annotation->estimate = $input->estimate;
			
			$final_checksum = md5($annotation->__toString());
			
			// dirty?
			if($initial_checksum != $final_checksum)
			{
				if($annotation->save())
				{
					$response->ok = true;
					$response->id = $annotation->_id;
				}
				else
				{
					$input->addValidator(new AMErrorValidator('error', 'Could not save task') );
					$this->hydrateErrors($input, $response);
				}
			}
			else
			{
				$response->ok = true;
				$response->id = $annotation->_id;
			}
		}
	}
	
	private function updateNote(&$array, &$input, &$response)
	{
		$annotation = YSSNote::noteWithArray($array);
		
		$initial_checksum = md5($annotation->__toString());
		$final_checksum   = null;
		
		$this->updateBaseAnnotation($annotation, $input, $response);
		
		$final_checksum = md5($annotation->__toString());
		
		// dirty?
		if($initial_checksum != $final_checksum)
		{
			if($annotation->save())
			{
				$response->ok = true;
				$response->id = $annotation->_id;
			}
			else
			{
				$input->addValidator(new AMErrorValidator('error', 'Could not save note') );
				$this->hydrateErrors($input, $response);
			}
		}
		else
		{
			$response->ok = true;
			$response->id = $annotation->_id;
		}
	}
	
	private function updateBaseAnnotation(&$annotation, &$input, &$response)
	{
		if($input->type && $annotation->type != $input->type)
			$annotation->type = $input->type;
		
		if($input->label && $annotation->label != $input->label)
			$annotation->label = $input->label;
		
		if($input->description && $annotation->description != $input->description)
			$annotation->description = $input->description;
			
		if($input->context && $annotation->context != $input->context)
			$annotation->context = $input->context;
		
		if($input->x && $annotation->x != $input->x)
			$annotation->x = $input->x;
		
		if($input->y && $annotation->y != $input->y)
			$annotation->y = $input->y;
		
		if($input->width && $annotation->width != $input->width)
			$annotation->width = $input->width;
			
		if($input->height && $annotation->height != $input->height)
			$annotation->height = $input->height;
	}
	
	public function deleteAnnotation($project_id, $view_id, $state_id, $annotation_id)
	{
		$response     = new stdClass();
		$response->ok = false;
		
		$project_id = YSSUtils::transform_to_id($project_id);
		$view_id    = YSSUtils::transform_to_id($view_id);
		$state_id   = YSSUtils::transform_to_id($state_id);
		
		$data                  = array();
		$data['view_id']       = $view_id;
		$data['project_id']    = $project_id;
		$data['state_id']      = $state_id;
		$data['annotation_id'] = $annotation_id;
		
		$context = array(AMForm::kDataKey=>$data);
		$input   = AMForm::formWithContext($context);
		
		$this->applyBaseAnnotationValidators($input);
		$input->addValidator(new AMPatternValidator('annotation_id', AMValidator::kRequired, '/^[a-z0-9]{32}$/', "Invalid annotation id."));
		
		if($input->isValid)
		{
			$session  = YSSSession::sharedSession();
			$database = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
		
			$annotation = $database->document('project/'.$project_id.'/'.$view_id.'/'.$state_id.'/'.$annotation_id);
		
			if($annotation && !isset($annotation['error']))
			{
				$result = $database->delete($annotation['_id'], $annotation['_rev']);
			
				if(isset($result['error']))
				{
					$input->addValidator(new AMErrorValidator('error', 'Unable to delete annotation') );
					$this->hydrateErrors($input, $response);
				}
				else
				{
					$response->ok = true;
					$response->id = $annotation['_id'];
				}
			}
			else
			{
				$input->addValidator(new AMErrorValidator('error', 'Invalid annotation') );
				$this->hydrateErrors($input, $response);
			}
		}
		else
		{
			$input->addValidator(new AMErrorValidator('error', 'Invalid annotation') );
			$this->hydrateErrors($input, $response);
		}
		
		echo json_encode($response);
	}
}

$manager  = new AMServiceManager();
$manager->bindContract(new YSSServiceAnnotations());
$manager->start();
?>