<?php
/* PRODEST-MASTER:
{
	"name": "wpdigi-utils.php",
	"description": "Fichier contenant les outils généraux pour l'extension / File containing general tools for plugin",
	"type": "file",
	"check": true,
	"author":
	{
		"email": "dev@evarisk.com",
		"name": "Alexandre T"
	},
	"version": 0.1
}
*/

/* PRODEST:
{
	"name": "wpdigi_utils",
	"description": "Classe contenant les outils généraux pour l'extension / CLass containing general tools for pplugin",
	"type": "class",
	"check": true,
	"author":
	{
		"email": "dev@evarisk.com",
		"name": "Alexandre T"
	},
	"version": 0.1
}
*/
class wpdigi_utils {

	/* PRODEST:
	{
		"name": "__construct",
		"description": "CORE - Instanciation de la classe / Object instanciation",
		"type": "function",
		"check": true,
		"author":
		{
			"email": "dev@evarisk.com",
			"name": "Alexandre T"
		},
		"version": 0.1
	}
	*/
	function __construct() {}

	/**
	 * INTERNAL LIB - Check and get the template file path to use for a given display part
	 *
	 PRODEST:
	 {
		"name": "get_template_part",
		"description": "INTERNAL LIB - Vérifie et récupère si il existe le fichier template pour le bloc a afficher / Check and get the template file path to use for a given display part ",
		"type": "function",
		"check": true,
		"author":
		{
			"email": "dev@evarisk.com",
			"name": "Alexandre T"
		},
		param:
		{
			'$plugin_dir_name':{'type': 'string', 'description': 'The main directory name containing the plugin', 'default': 'null'},
			'$main_template_dir':{'type': 'string', 'description': 'The main directory name containing the plugin', 'default': 'null'},
			'$side':{'type': 'string', 'description': 'The website part were the template will be displayed. Backend or frontend', 'default': 'null'},
			'$slug':{'type': 'string', 'description': 'The slug name for the generic template.', 'default': 'null'},
			'$name':{'type': 'string', 'description': 'The name of the specialised template.', 'default': 'null'},
		},
		return:
		{
			'$path':{ 'type': 'string', 'description': 'The template file path to use if founded' }
		}
		"version": 0.1
	 }
	 *
	 * @uses locate_template()
	 * @uses get_template_part()
	 *
	 * @param string $plugin_dir_name The main directory name containing the plugin
	 * @param string $main_template_dir THe main directory containing the templates used for display
	 * @param string $side The website part were the template will be displayed. Backend or frontend
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 *
	 * @return string The template file path to use
	 *
	 */
	static function get_template_part( $plugin_dir_name, $main_template_dir, $side, $slug, $name = null, $debug = null ) {
		$path = '';

		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		$templates[] = "{$side}/{$slug}.php";

		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $plugin_dir_name . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );

		/**	Allow debugging	*/
		if ( !empty( $debug ) ) {
			echo '--- Debug mode - Start ---<br/>';
			echo __FILE__ . '<br/>';
			echo 'Debug for display method<br/>';
		}

		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;

				/**	Allow debugging	*/
				if ( !empty( $debug ) ) {
					echo __LINE__ . ' - ' . $main_template_dir . $template_name . '<hr/>';
				}

				if ( file_exists( $main_template_dir . $template_name ) ) {
					$path = $main_template_dir . $template_name;
					break;
				}
			}
		}

		/**	Allow debugging	*/
		if ( !empty( $debug ) ) {
			echo '--- Debug mode - END ---<br/><br/>';
		}

		return $path;
	}

	/* PRODEST:
	{
		"name": "activation",
		"description": "CORE - Lance des actions spécifiques lors de l'activation de l'extension / Make some specific action on plugin activation",
		"type": "function",
		"check": true,
		"author":
		{
			"email": "dev@evarisk.com",
			"name": "Alexandre T"
		},
		"version": 0.1
	}
	*/
	public static function activation() {
		do_action( 'digi-extra-module-activation' );

		flush_rewrite_rules( false );/**	False allow to avoid htaccess rewriting	*/
	}

}


?>