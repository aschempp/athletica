function order_query(field){
	order_dir = (order_by==field) ? ((order_dir=='ASC') ? 'DESC' : 'ASC') : 'ASC';
	order_by = field;

	document.location.href = uri_order+'order_by='+order_by+'&order_dir='+order_dir;
}

function reset_filter(page){
	document.location.href = page;
}

function show_hide_filter(message_id){
	if(document.getElementById('box_message_'+message_id)){
		document.getElementById('box_message_'+message_id).className = (document.getElementById('box_message_'+message_id).className=='message') ? 'message hidden' : 'message';
	}
}