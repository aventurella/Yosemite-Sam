function(head, req)
{
	var row;
	var result  = null;
	var current;
	
	while(row = getRow())
	{
		var type  = row.value.type;
		
		if(type == "project")
		{
			if(current)
			{
				if(!result)
				{
					result = [current];
				}
				else
				{
					result.push(current);
				}
			}
				
			current             = row.value;
			current.tasks       = {"completed":0, "total":0};
			current.views       = 0;
			current.attachments = [];
		}
		else
		{
			switch(type)
			{
				case "attachment":
					current.attachments.push({"content_type":row.value.content_type, "path":row.value.path, "label":row.value.label, "_id": row.value._id});
					break;
						
				case "view":
					current.views = current.views + 1;
					break;
				
				case "task":
					current.tasks.total = current.tasks.total + 1;
					current.tasks.completed = current.tasks.completed + row.value.value;
					break;
			}
		}
	}
	
	result.push(current);
	send(JSON.stringify(result));
}