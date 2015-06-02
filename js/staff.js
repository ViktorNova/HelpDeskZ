$(document).ready(function(){
	$("#navbar span").click(function(){
		$("#navbar span").parent().removeClass('current');
		$(this).parent().addClass('current');
		$("#submenu ul").hide();
		$("#s"+this.id).show();
	});
	$("#home_tabs li").click(function(){
		$("#home_tabs li").removeClass('active');
		$(this).addClass('active');
		$("#home_tabs_content div.home_tab_content").hide();
		$("."+this.id).show();
	});
	$('#quicktabs').jrtabs();
	$('#tabs').jrtabs();
	$('#tabs2').jrtabs();
	
	$("#selectall").change(function () {
		$(".checkall").prop('checked', $(this).prop("checked"));
		countchecked();
	});
	$(".checkall").click(function(){
		countchecked();
	});
	
	 $( "#date_from" ).datepicker({
	onClose: function( selectedDate ) {
	$( "#date_to" ).datepicker( "option", "minDate", selectedDate );
	}
	});
	$( "#date_to" ).datepicker({
	onClose: function( selectedDate ) {
	$( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
	}
	});
	
	 container_dialog = $( "#container_dialog" ).dialog({
		autoOpen: false,
		width: 670,
		minWidth:670,
		modal: true,
		resizable:false,

	});	

});

function countchecked() {
	var n = $( "input.checkall:checked" ).length;
	if(n >= 1){
		$("#options").show();
	}else{
		$("#selectall").attr("checked",false);
		$("#options").hide();	
	}
}

function init_loader(){
	dialogloader = $( "#dialog_loader" ).dialog({
	dialogClass: "no-close",	
	modal:true,
	minHeight:50,
	resizable: false
	});
}
function stop_loader(){
	dialogloader.dialog( "close" );		
}
/* Manage Tickets Tab */
function quote_post(_ticketID,_ticketPostID){
	init_loader();
	$.ajax({
		type:    'POST',
		url:     _baseURL + '/tickets/view/' + _ticketID + '/GetQuote/' + _ticketPostID,
		data:    '',
		success: function(_data) {
			$("#message").val($("#message").val() + _data);
			$("#tabs").jrchangetab(2);
			$('html, body').animate({scrollTop : 0},800);
			stop_loader();
		}
	});	
}
function edit_post(_ticketPostID){
	init_loader();
	$("#container_dialog").dialog('option', 'title', 'Edit post');
	$("#container_dialog").load(_msgURL + _ticketPostID, function(){
		container_dialog.dialog( "open" );
		stop_loader();
	});
}
function addKnowledgebase(_kbID){
	init_loader();
	$.ajax({
		type:    'POST',
		url:     _kbURL + _kbID,
		data:    '',
		success: function(_data) {
			$("#message").val($("#message").val()+_data);
			stop_loader();
			$("#knowledgebaseList").val("");
		}
	});	
}
function remove_post(_ticketPostID){
	if(confirm('Are you sure you wish to continue?')){
		$.ajax({
			type:    'POST',
			url:     _delmsgURL + _ticketPostID,
			data:    '',
			success: function(_data) {
				$("#msg_"+_ticketPostID).remove();
			}
		});
	}
}
/* Canned Tab */
function showCannedForm(_cannedID){
	init_loader();
	if(_cannedID == 0){
		$("#container_dialog").dialog('option', 'title', 'Add new canned response');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit Canned Response');
	}
	$("#container_dialog").load(_cannedURL+_cannedID, function(){
		container_dialog.dialog( "open" );
		stop_loader();		
	});
}
/* Knowledgebase Tab */
function showKBCategoryForm(_knowledgeID, _parentID){
	init_loader();
	if(_knowledgeID == 0){
		$("#container_dialog").dialog('option', 'title', 'Insert New Knowledgebase Category');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit Knowledgebase Category');
	}

	$("#container_dialog").load(_kbURL+_knowledgeID, function(){
		$('#parentselector option:selected').attr('selected',false);
		$('#parentselector option[value="'+_parentID+'"]').attr('selected', 'selected');		
		container_dialog.dialog( "open" );
		stop_loader();		
	});	
}
/* Tickets Tab */
function showFileTypeForm(_fileID){
	init_loader();
	if(_fileID == 0){
		$("#container_dialog").dialog('option', 'title', 'Insert File Type');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit File Type');
	}
	$("#container_dialog").load(_fileTypeUrl+_fileID, function(){
		container_dialog.dialog( "open" );
		stop_loader();		
	});
}
function showCustomFieldForm(_fieldID){
	init_loader();
	if(_fieldID == 0){
		$("#container_dialog").dialog('option', 'title', 'Add new custom field');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit custom field');
	}
	$("#container_dialog").load(_customFieldUrl+_fieldID, function(){
		container_dialog.dialog( "open" );
		stop_loader();
	});
}
function addCustomFieldNext(){
	type = $('input[name=field_type]:checked').val();
	if(type !== undefined){
		$("#customfield_step1").hide();
		$("#customfield_"+type).show();	
	}
}
function addCustomFieldBack(type){
	$("#customfield_"+type).hide();	
	$("#customfield_step1").show();
}
/* Departments Tab */
function showDepartmentsForm(_departmentID){
	init_loader();
	if(_departmentID == 0){
		$("#container_dialog").dialog('option', 'title', 'Add new department');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit department');
	}
	$("#container_dialog").load(_departmentUrl+_departmentID, function(){
		container_dialog.dialog( "open" );
		stop_loader();		
	});
}
/* Staff Tab */
function showStaffForm(_staffID){
	init_loader();
	if(_staffID == 0){
		$("#container_dialog").dialog('option', 'title', 'Add new account');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit account');
	}
	$("#container_dialog").load(_staffUrl+_staffID, function(){
		container_dialog.dialog( "open" );
		stop_loader();		
	});
}
/* Users Tab */
function showUserForm(_userID){
	init_loader();
	if(_userID == 0){
		$("#container_dialog").dialog('option', 'title', 'Add new user');
	}else{
		$("#container_dialog").dialog('option', 'title', 'Edit user');
	}
	$("#container_dialog").load(_usersURL+_userID, function(){
		container_dialog.dialog( "open" );
		stop_loader();		
	});
}

/* Social */
function checksocialdetails(type)
{
    if(type == 'facebook')
    {
        var socialType = $("#facebookoauth").val();
        if(socialType == 1)
        {
            $("#facebook_tr1").show();
            $("#facebook_tr2").show();
        }else{
            $("#facebook_tr1").hide();
            $("#facebook_tr2").hide();
        }
    }else if(type == 'google')
    {
        var socialType = $("#googleoauth").val();
        if(socialType == 1)
        {
            $("#google_tr1").show();
            $("#google_tr2").show();
            $("#google_tr3").show();
        }else{
            $("#google_tr1").hide();
            $("#google_tr2").hide();
            $("#google_tr3").hide();
        }
    }
}