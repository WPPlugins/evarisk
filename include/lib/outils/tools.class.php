<?php
/**
* Plugin tools management
*
* Define the different tools available in the plugin
* @author Evarisk <dev@evarisk.com>
* @version 5.1.4.5
* @package Digirisk
* @subpackage librairies
*/

/**
* Define the different tools available in the plugin
* @package Digirisk
* @subpackage librairies
*/
class digirisk_tools
{

	/**
	*	Create the html ouput code for the tools page
	*
	*	@return The html code to output for tools page
	*/
	function main_page(){
		echo digirisk_display::start_page(__('Outils du logiciel Digirisk', 'evarisk'), EVA_OPTIONS_ICON, __('Outils du logiciel', 'evarisk'), __('Outils du logiciel', 'evarisk'), TABLE_OPTION, false, '', false, true);
?>
<div id="digirisk_configurations_container" class="clear" >
	<div id="tools_tabs" >
		<ul>
			<li><a href="#digirisk_notification_tab" title="digirisk_tools_tab_container" ><?php _e('Notifications', 'evarisk'); ?></a></li>
			<li class="loading_pic_on_select" ><a href="<?php echo  EVA_INC_PLUGIN_URL; ?>ajax.php?post=true&amp;nom=tools&amp;action=db_manager" title="digirisk_tools_tab_container" ><?php _e('V&eacute;rification de la base de donn&eacute;es', 'evarisk'); ?></a></li>
<?php
	if(current_user_can('digi_delete_database')){
?>
			<li class="loading_pic_on_select" ><a href="<?php echo  EVA_INC_PLUGIN_URL; ?>ajax.php?post=true&amp;nom=tools&amp;action=db_reinit" title="digirisk_tools_tab_container" ><?php _e('R&eacute;initialisation de la base de donn&eacute;es', 'evarisk'); ?></a></li>
<?php
	}
?>
		</ul>
		<div id="digirisk_notification_tab" ><?php echo digirisk_admin_notification::admin_message(); ?></div>
		<div id="digirisk_tools_tab_container" >&nbsp;</div>
	</div>
</div>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#tools_tabs").tabs({
			load: function( event, ui ){
				jQuery("#digirisk_tools_tab_container").html( "" );
			},
		});
		jQuery(".loading_pic_on_select a").click(function(){
			jQuery("#digirisk_tools_tab_container").html(jQuery("#loadingImg").html());
			jQuery("#digi_option_submit_button").hide();
		});

		jQuery(".digi_repair_db_version").live("click", function(){
			jQuery(this).after(jQuery("#round_loading_img div").html());
			var data = {
				action: "digi_ajax_repair_db",
				digi_ajax_nonce: '<?php echo wp_create_nonce("digi_repair_db_per_version"); ?>',
				version_id: jQuery(this).attr("id").replace("digi_repair_db_version_", ""),
			};
			jQuery.post(ajaxurl, data, function(response){
				if (response[0]) {
					jQuery("#digirisk_tools_tab_container").load("<?php echo EVA_INC_PLUGIN_URL ?>ajax.php", {
						"post": "true",
						"nom": "tools",
						"action": "db_manager",
					});
				}
				else {
					alert(digi_html_accent_for_js("<?php _e('Une erreur est survenue lors de la tentative de r&eacute;paration de la base de donn&eacute;s', 'evarisk'); ?>"));
				}
			}, 'json');
		});

		jQuery(".digi_repair_db_datas_version").live("click", function(){
			if ( confirm(digi_html_accent_for_js("<?php _e('Attention\r\nSi vous effectuez cette op&eacute;ration alors que vous n\'avez pas relev&eacute; de probl&egrave;me sur votre installation, vous risquez d\'endommager celle ci en y ins&eacute;rant des donn&eacute;es d&eacute;j&agrave; existante', 'evarisk'); ?>")) ) {
				jQuery(this).after(jQuery("#round_loading_img div").html());
				var data = {
					action: "digi_ajax_repair_db_datas",
					digi_ajax_nonce: '<?php echo wp_create_nonce("digi_repair_db_per_version"); ?>',
					version_id: jQuery(this).attr("id").replace("digi_repair_db_datas_version_", ""),
				};
				jQuery.post(ajaxurl, data, function(response){
					if (response[0]) {
						jQuery("#digirisk_tools_tab_container").load("<?php echo EVA_INC_PLUGIN_URL ?>ajax.php", {
							"post": "true",
							"nom": "tools",
							"action": "db_manager",
						});
						actionMessageShow("#message", digi_html_accent_for_js("<?php _e('La correction a dient &eacute;t&eacute; effectu&eacute;e', 'evarisk'); ?>"));
						goTo("#message");
					}
					else {
						alert(digi_html_accent_for_js("<?php _e('Une erreur est survenue lors de la tentative de r&eacute;paration de la base de donn&eacute;s', 'evarisk'); ?>"));
					}
				}, 'json');
			}
		});
	});
</script>
<?php
		echo digirisk_display::end_page();
	}

		/*	CLEAN UP A VAR BEFORE SENDING IT TO OUTPUT OR DATABASE	*/
	function IsValid_Variable($MyVar2Test,$DefaultValue='')
	{
		$MyVar = (trim(strip_tags(stripslashes($MyVar2Test)))!='') ? trim(strip_tags(stripslashes(($MyVar2Test)))) : $DefaultValue ;
		$MyVar = html_entity_decode(str_replace("&rsquo;", "'", htmlentities($MyVar, ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8');

		return $MyVar;
	}

	function slugify_nospace($text)
	{
	  if (empty($text))
	  {
		return '';
	  }else{

	   $text = preg_replace('/\s/', '+', $text);
	   $text = trim($text);

	  }

	  return $text;
	}

	function slugify($text)
	{
		$pattern = Array("�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�");
		$rep_pat = Array("e", "e", "e", "c", "a", "a", "i", "i", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U");
		if(!(empty($text)))
		{
			$text = str_replace($pattern, $rep_pat, utf8_decode($text));
			$text = preg_replace('/\s/', '_', $text);
			$text = trim($text);
		}
		return $text;
	}

	function slugify_accent($text){
	  return remove_accents(utf8_decode($text));
	}

	function slugify_noaccent($text){
	  return remove_accents($text);
	}

	function slugify_noaccent_no_utf8decode($text){
		$pattern  = Array("/&eacute;/", "/&egrave;/", "/&ecirc;/", "/&ccedil;/", "/&agrave;/", "/&acirc;/", "/&icirc;/", "/&iuml;/", "/&ucirc;/", "/&ocirc;/", "/&Egrave;/", "/&Eacute;/", "/&Ecirc;/", "/&Euml;/", "/&Igrave;/", "/&Iacute;/", "/&Icirc;/", "/&Iuml;/", "/&Ouml;/", "/&Ugrave;/", "/&Ucirc;/", "/&Uuml;/", "/é/", "/è/", "/ê/", "/ç/", "/à/", "/â/", "/ï/", "/î/", "/ù/", "/û/", "/ô/", "/É/", "/È/", "/Ê/", "/Ë/", "/Ì/", "/Í/", "/Î/", "/Ï/", "/Ö/", "/Û/", "/Ù/", "/Ü/");
		$rep_pat = Array("e", "e", "e", "c", "a", "a", "i", "i", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U","e", "e", "e", "c", "a", "a", "i", "i", "u", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U");
		if ($text == '')
		{
			return '';
		}
		else
		{
			$text = preg_replace($pattern, $rep_pat, $text);
	  }

	  return $text;
	}

	function stripAccents($string)
	{
		$newString = str_replace(array('�', '�', '�', '�', '�'), 'a', $string);
		$newString = str_replace(array('�', '�', '�', '�', '�'), 'A', $newString);
		$newString = str_replace(array('�', '�', '�', '�'), 'e', $newString);
		$newString = str_replace(array('�', '�', '�', '�'), 'E', $newString);
		$newString = str_replace(array('�', '�', '�', '�'), 'i', $newString);
		$newString = str_replace(array('�', '�', '�', '�'), 'I', $newString);
		$newString = str_replace(array('�', '�', '�', '�', '�'), 'o', $newString);
		$newString = str_replace(array('�', '�', '�', '�', '�'), 'O', $newString);
		$newString = str_replace(array('�', '�', '�', '�'), 'u', $newString);
		$newString = str_replace(array('�', '�', '�', '�'), 'U', $newString);
		$newString = str_replace(array('�', '�'), 'y', $newString);
		$newString = str_replace(array('�', '�'), 'Y', $newString);
		$newString = str_replace('�', 'c', $newString);
		$newString = str_replace('�', 'C', $newString);
		$newString = str_replace('�', 'n', $newString);
		$newString = str_replace('�', 'N', $newString);
		$newString = str_replace('n�', '', $newString);
		$newString = str_replace('�', '_', $newString);
		return $newString;
	}

	public static function copyEntireDirectory($sourceDirectory, $destinationDirectory)
	{
		if(is_dir($sourceDirectory))
		{
			if(!is_dir($destinationDirectory))
			{
				mkdir($destinationDirectory, 0755, true);
				exec('chmod -R 755 ' . EVA_GENERATED_DOC_DIR);
			}
			$hdir = opendir($sourceDirectory);
			while($item = readdir($hdir))
			{
				if(is_dir($sourceDirectory . '/' . $item) && ($item != '.') && ($item != '..')  && ($item != '.svn') )
				{
					digirisk_tools::copyEntireDirectory($sourceDirectory . '/' . $item, $destinationDirectory . '/' . $item);
				}
				elseif(is_file($sourceDirectory . '/' . $item))
				{
					copy($sourceDirectory . '/' . $item, $destinationDirectory . '/' . $item);
				}
			}
			closedir( $hdir );
		}
	}

	//couleur al�atoire g�n�r�e
	function getColor(){
		$a = DecHex(mt_rand(0,15));
		$b = DecHex(mt_rand(0,15));
		$c = DecHex(mt_rand(0,15));
		$d = DecHex(mt_rand(0,15));
		$e = DecHex(mt_rand(0,15));
		$f = DecHex(mt_rand(0,15));

		$hexa = $a . $b . $c . $d . $e . $f;

		return $hexa;
	}

	//couleur du texte en fonction de la couleur g�n�r�e
	function getContrastColor($color){
		return (hexdec($color) > 0xffffff/2) ? '000000' : 'ffffff';
	}

	function db_deletion(){
		$output = '';

?>
<br class="clear" />
<span class="tools_alert db_reinit" ><?php _e('Attention, cette interface permet de r&eacute;initialiser la base de donn&eacute;es (Suppression de toutes les donn&eacute;s pr&eacute;sentes dans la base). Cette op&eacute;ration ne peut &ecirc;tre invers&eacute;e par la suite', 'evarisk'); ?>.<br/><?php _e('Faites une sauvegarde de votre base de donn&eacute;es avant toute op&eacute;ration', 'evarisk'); ?>.</span>
<br/>
<br/><input type="checkbox" name="auto_redirect_to_digi_install" id="auto_redirect_to_digi_install" value="yes" checked="checked" />&nbsp;<label for="auto_redirect_to_digi_install" ><?php _e('Redirection automatique vers la page d\'installation apr&egrave;s r&eacute;initialisation de la base de donn&eacute;es', 'evarisk'); ?></label><br/><button type="button" id="digi_reinit_db" class="button-secondary" ><?php _e('R&eacute;initialiser la base', 'evarisk'); ?></button>
<script type="text/javascript" >
	digirisk(document).ready(function(){
		jQuery("#digi_reinit_db").click(function(){
			if(confirm(digi_html_accent_for_js("<?php _e('&Ecirc;tes vous s&ucirc;r de vouloir r&eacute;initialiser la base de donn&eacute;es? Cette op&eacute;ration supprimera le contenu du logiciel digirisk', 'evarisk'); ?>"))){
				var digi_redirect_to_install = 'yes';
				if(!jQuery("#auto_redirect_to_digi_install").is(":checked")){
					digi_redirect_to_install = 'no';
				}
				jQuery("#digi_reinit_db").after(jQuery("#round_loading_img div").html());
				jQuery("#digi_reinit_db").remove();
				jQuery("#digirisk_tools_tab_container").load("<?php echo EVA_INC_PLUGIN_URL; ?>ajax.php",{
					"post":"true",
					"nom":"tools",
					"action":"db_reinit_launch",
					"redirect":digi_redirect_to_install
				});
			}
		});
	});
</script>
<?php

		return $output;
	}

}
