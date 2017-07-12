<?php
/**
 * Notes management file
 *
 * @author Evarisk
 * @version v5.0
 */


/**
 * Notes management class
 *
 * @author Evarisk
 * @version v5.0
 */
class evaNotes
{
	/**
	*	Generate the dialog html for note taker
	*
	*	@return mixed $noteDialog The html output for the note dialog box
	*/
	public static function noteDialogMaker()
	{
		$noteDialog = '
		<div id="noteTaker" >
			<i class="noteTakerPic dashicons dashicons-welcome-write-blog" title="' . __('Cliquez ici pour prendre des notes', 'evarisk') . '"  ></i>
			<div id="digiNotes" class="hide" title="' . __('Prise rapide de notes', 'evarisk') . '" >&nbsp;</div>
		</div>';

		return $noteDialog;
	}

	/**
	*	Generate the notes form with the different element
	*
	*	@return mixed $dialogFrom The form html output
	*/
	function noteDialogForm()
	{
		global $current_user;
		$noteContent = '';

		if(!is_dir(EVA_NOTES_PLUGIN_DIR))
		{
			wp_mkdir_p(EVA_NOTES_PLUGIN_DIR);
		}
		elseif(is_file(EVA_NOTES_PLUGIN_DIR . 'user-' . $current_user->ID . '_Notes.txt'))
		{
			$noteContent = file_get_contents(EVA_NOTES_PLUGIN_DIR . 'user-' . $current_user->ID . '_Notes.txt');
		}
		$noteContent = stripslashes($noteContent);

		$dialogFrom =
				'<div id="noteSaverMessage" >&nbsp;</div>
<textarea rows="3" cols="10" name="digiNotesInput" id="digiNotesInput" class="noteInput" >' . $noteContent . '</textarea>';

		return $dialogFrom;
	}

	/**
	*	Generate the script allowing to make the box become a dialog box
	*
	*	@return mixed $noteDialogScript The script output to transform a box into a dialog box
	*/
	public static function noteDialogScriptMaker()
	{
		$noteDialogScript = '
				digirisk("#digiNotes").html(digirisk("#loadingImg").html());
				digirisk("#noteTaker").click(function(){
					digirisk("#digiNotes").dialog("open");
					digirisk("#digiNotes").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
					{
						"post":"true",
						"act":"loadDiginote"
					});
				});

				digirisk("#digiNotes").dialog({
					autoOpen: false,
					height: 500,
					width: 500,
					modal: true,
					buttons:{
						"' . __('Annuler', 'evarisk') . '": function(){
							digirisk(this).dialog("close");
						},
						"' . __('Enregistrer', 'evarisk') . '": function(){
							digirisk("#ajax-response").load("' . EVA_INC_PLUGIN_URL . 'ajax.php",
							{
								"post":"true",
								"act":"saveDigiNote",
								"notesContent": digirisk("#digiNotesInput").val()
							});
						}
					},
					close: function(){
						digirisk("#digiNotes").html(digirisk("#loadingImg").html());
					}
				});';
		return $noteDialogScript;
	}

	/**
	*	Save the note taken by the current user into a file
	*
	*	@param mixed $noteContent The text that the current user send throught the dialog box
	*/
	function saveDigiNote($noteContent)
	{
		global $current_user;

		/*	Check if the dir exist, if not create it before trying to save file	*/
		if(!is_dir(EVA_NOTES_PLUGIN_DIR))
		{
			wp_mkdir_p(EVA_NOTES_PLUGIN_DIR);
		}

		/*	Write the text file with the user notes	*/
		if(file_put_contents(EVA_NOTES_PLUGIN_DIR . 'user-' . $current_user->ID . '_Notes.txt' ,$noteContent))
		{
			$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'success_vs.png\' class=\'messageIcone\' alt=\'succes\' />' . __('Les notes ont &eacute;t&eacute; enregistr&eacute;es', 'evarisk');
		}
		else
		{
			$messageInfo = '<img src=\'' . EVA_IMG_ICONES_PLUGIN_URL . 'error_vs.png\' class=\'messageIcone\' alt=\'error\' />' . __('Les notes n\'ont pas pu &ecirc;tre enregistr&eacute;es', 'evarisk');
		}

echo
'<script type="text/javascript">
	digirisk(document).ready(function(){
		actionMessageShow("#noteSaverMessage", "' . $messageInfo . '");
		setTimeout(function(){
			actionMessageHide("#noteSaverMessage");
			digirisk("#digiNotes").dialog("close");
		}
		,2000);
	});
</script>';

	}

}