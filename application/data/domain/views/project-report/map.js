function(doc) 
{
	// !json enums.TaskStatus
	
	var project = doc._id.split("/").slice(0, 2).join("/");
	
	if(doc.type == "project")
	{    
		emit([project, 0], doc)
	}
	
	if (doc.type == "view")
	{
		emit([project, 1], {"type":"view", "value":1});
	}
	
	if (doc.type == "task")
	{
		var taskValue;
		switch(doc.status)
		{
			case enums.TaskStatus.kStatusIncomplete:
				taskValue = 0;
				break;

			case enums.TaskStatus.kStatusComplete:
				taskValue = 1;
				break;
		}
		
		emit([project, 2], {"type":"task", "value":taskValue})
	}
	
	if (doc.type == "attachment")
	{
		// we only want project attachments, this would give us ALL attachments within a project
		// eg: including state attachments, which we don't want here.
		// so we apply a simple rule to omit those bad boys
		if(doc._id.split('/').length == 4)
			emit([project, 3], {type:"attachment", "content_type":doc.content_type, "label":doc.label, "path":doc.path, "_id":doc._id.split("/").slice(-1)})
	}
	
	
	
}
