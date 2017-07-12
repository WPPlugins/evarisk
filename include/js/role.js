/**
*
*/
function saveRightForUsers(tableElement, idElement, dbTable, outputMessage, tableContainer)
{

	digirisk("#listeIndividusPourDroits" + tableElement + idElement + "_filter input").focus();
	digirisk("#listeIndividusPourDroits" + tableElement + idElement + "_filter input").select();
	digirisk("#listeIndividusPourDroits" + tableElement + idElement + "_filter input").val("");
	digirisk("#listeIndividusPourDroits" + tableElement + idElement + "_filter input").keyup();

	digirisk("#userRightDetail_see").val("");
	digirisk(".see").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_see").val( digirisk("#userRightDetail_see").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_see_", "") + "#!#" );
		}
	});

	digirisk("#userRightDetail_recursif").val("");
	digirisk(".recursif").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_recursif").val( digirisk("#userRightDetail_recursif").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_recursif", "") + "#!#" );
		}
	});

	digirisk("#userRightDetail_delete").val("");
	digirisk(".delete").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_delete").val( digirisk("#userRightDetail_delete").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_delete_", "") + "#!#" );
		}
	});

	digirisk("#userRightDetail_edit").val("");
	digirisk(".edit").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_edit").val( digirisk("#userRightDetail_edit").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_edit_", "") + "#!#" );
		}
	});
	
	digirisk("#userRightDetail_add_gpt").val("");
	digirisk(".add_groupement").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_add_gpt").val( digirisk("#userRightDetail_add_gpt").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_add_gpt", "") + "#!#" );
		}
	});
	
	digirisk("#userRightDetail_add_unit").val("");
	digirisk(".add_unite").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_add_unit").val( digirisk("#userRightDetail_add_unit").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_add_unit", "") + "#!#" );
		}
	});

	digirisk("#userRightDetail_add_task").val("");
	digirisk(".add_task").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_add_task").val( digirisk("#userRightDetail_add_task").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_add_task", "") + "#!#" );
		}
	});

	digirisk("#userRightDetail_add_action").val("");
	digirisk(".add_action").each(function(){
		if(digirisk(this).is(":checked")){
			digirisk("#userRightDetail_add_action").val( digirisk("#userRightDetail_add_action").val() + digirisk(this).val() + "!#!" + digirisk(this).attr("id").replace("user_add_action", "") + "#!#" );
		}
	});

	digirisk("#ajax-response").load(EVA_AJAX_FILE_URL, 
	{
		"post": "true", 
		"table": dbTable,
		"act": "save",
		"message": outputMessage,
		"tableContainer": tableContainer,

		"user_recursif": digirisk("#userRightDetail_recursif").val(),

		"user_see": digirisk("#userRightDetail_see").val(),
		"user_delete": digirisk("#userRightDetail_delete").val(),
		"user_edit": digirisk("#userRightDetail_edit").val(),
		"user_add_gpt": digirisk("#userRightDetail_add_gpt").val(),
		"user_add_unit": digirisk("#userRightDetail_add_unit").val(),
		"user_add_task": digirisk("#userRightDetail_add_task").val(),
		"user_add_action": digirisk("#userRightDetail_add_action").val(),

		"user_see_old": digirisk("#userRightDetail_see_old").val(),
		"user_delete_old": digirisk("#userRightDetail_delete_old").val(),
		"user_edit_old": digirisk("#userRightDetail_edit_old").val(),
		"user_add_gpt_old": digirisk("#userRightDetail_add_gpt_old").val(),
		"user_add_unit_old": digirisk("#userRightDetail_add_unit_old").val(),
		"user_add_task_old": digirisk("#userRightDetail_add_task_old").val(),
		"user_add_action_old": digirisk("#userRightDetail_add_action_old").val(),

		"tableElement": tableElement,
		"idElement": idElement
	});

}