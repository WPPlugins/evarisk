<?php

	function getScriptPartieDroite($pageHook)
	{
		return '
			<script type="text/javascript">
				digirisk(document).ready(function(){
					digirisk(".if-js-closed").removeClass("if-js-closed").addClass("closed");
					digirisk(".postbox h3, .postbox .handlediv").each(function(){digirisk(this).unbind("click");});
					postboxes.add_postbox_toggles("' . $pageHook . '");
					postboxes.init("' . $pageHook . '");
					postboxes.save_state("' . $pageHook . '");
					postboxes.save_order("' . $pageHook . '");

					jQuery( "#mainPostBox" ).removeClass( "hide-if-js" );
				});
			</script>';
	}

?>