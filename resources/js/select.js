function deselect(id) {
	$(id).getElements('tr td.checkbox input').each(function(element){element.checked = false;})
}

function select(id, e, select_type) {
	var e = new Event(e);

	switch (select_type)
	{
		case 'all' :
			$(id).getElements('tr td.checkbox input').each(function(element){element.checked = true;})
		break;
		case 'none' :
			deselect(id);
		break;
		case 'enabled' :
			if (!e.shift)
				deselect(id);

			$(id).getElements('tr.enabled td.checkbox input').each(function(element){
				element.checked = true; 
			})
		break;
		case 'disabled' :
			if (!e.shift)
				deselect(id);

			$(id).getElements('tr.enabled td.checkbox input').each(function(element){
				element.checked = true;
			})
		break;
	}
	
	return false;
}