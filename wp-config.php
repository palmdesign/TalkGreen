<?php
/**
 * A configuração de base do WordPress
 *
 * Este ficheiro define os seguintes parâmetros: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, e ABSPATH. Pode obter mais informação
 * visitando {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} no Codex. As definições de MySQL são-lhe fornecidas pelo seu serviço de alojamento.
 *
 * Este ficheiro contém as seguintes configurações:
 *
 * * Configurações de  MySQL
 * * Chaves secretas
 * * Prefixo das tabelas da base de dados
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Definições de MySQL - obtenha estes dados do seu serviço de alojamento** //
/** O nome da base de dados do WordPress */
define( 'DB_NAME', 'talkgreendb' );

/** O nome do utilizador de MySQL */
define( 'DB_USER', 'root' );

/** A password do utilizador de MySQL  */
define( 'DB_PASSWORD', '' );

/** O nome do serviddor de  MySQL  */
define( 'DB_HOST', 'localhost' );

/** O "Database Charset" a usar na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O "Database Collate type". Se tem dúvidas não mude. */
define( 'DB_COLLATE', '' );

/**#@+
 * Chaves únicas de autenticação.
 *
 * Mude para frases únicas e diferentes!
 * Pode gerar frases automáticamente em {@link https://api.wordpress.org/secret-key/1.1/salt/ Serviço de chaves secretas de WordPress.org}
 * Pode mudar estes valores em qualquer altura para invalidar todos os cookies existentes o que terá como resultado obrigar todos os utilizadores a voltarem a fazer login
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY', '-0pP>cC%&]38?waNr[oi@qlI?E}m)g#Glj,GDuQ5,+k=/@yn5b,@S)|3z{[{Ab^Y' );
define( 'SECURE_AUTH_KEY', ':- ,:P&`=HGr0Wq}s.uuB`j=J3w?u^d{8*9L~x5Pc7d6qd%RPdy@6s,}}k7{]E5E' );
define( 'LOGGED_IN_KEY', '%#5@RbZ}CCU`~TaYhXNjmX#v]0Ibn)8KHe*L#bs+x%09ztPE$+IY6>(-px_X%?58' );
define( 'NONCE_KEY', 'Jv>uAF%!8,7tfmcSE^6tO_8tjY^x$mJU^@qWezL6Ty:0A#W8oirEE5jE=?$@c.]u' );
define( 'AUTH_SALT', 'MXp>=25T.^ ^2U.[YM6-Y;Wd0CAdMv4:p[5jtzQZjsI1&p7d[*N(9GK,h,HH}kUG' );
define( 'SECURE_AUTH_SALT', '9!@vwW2|52/Mdw(0aX3Fo$pc5J<d9b0bF3_7Dy/S%c/{ii!r$o!QErxjg$%5%bQ+' );
define( 'LOGGED_IN_SALT', '^VL]g$s}_AsqL&u^$sX4l4FWq|HK<tUT*D}Ie]UCC>W3Z;!gF1tyY866L_t`wG_L' );
define( 'NONCE_SALT', 'RYxG+0(^yQ9%F+r{p6LL}UGZY=lUKI|W$C0,>YR!Be7P=h!.woh)81SSMQ23G=zc' );

/**#@-*/

/**
 * Prefixo das tabelas de WordPress.
 *
 * Pode suportar múltiplas instalações numa só base de dados, ao dar a cada
 * instalação um prefixo único. Só algarismos, letras e underscores, por favor!
 */
$table_prefix = 'wp_tg';

/**
 * Para developers: WordPress em modo debugging.
 *
 * Mude isto para true para mostrar avisos enquanto estiver a testar.
 * É vivamente recomendado aos autores de temas e plugins usarem WP_DEBUG
 * no seu ambiente de desenvolvimento.
 *
 * Para mais informações sobre outras constantes que pode usar para debugging,
 * visite o Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* E é tudo. Pare de editar! */

/** Caminho absoluto para a pasta do WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Define as variáveis do WordPress e ficheiros a incluir. */
require_once( ABSPATH . 'wp-settings.php' );
