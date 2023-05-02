<?php
// chargement css theme enfant
// wp_enqueue_style : fonction wp qui charge la feuille de style
//wp_enqueue_scripts: fonction wp utilisée pour la mise en file d'attente des scripts et styles
function neve_child_load_css() {
	wp_enqueue_style( 'neve-child-style', get_stylesheet_uri(), array( 'neve-style' ), filemtime(get_stylesheet_directory()) . '/style.css');
}
add_action( 'wp_enqueue_scripts', 'neve_child_load_css' );


// CRÉATION DU LIEN ADMIN DU MENU SI USER CONNECTÉ
/* La fonction wpb_admin_menu prend deux arguments : $items (les éléments du menu de navigation )et $args (tableau associatif contenant les options/paramètre du menu: theme_location; l'id du menu; etc)*/
function wpb_admin_menu( $items, $args ) {
    
    // Vérifie si l'utilisateur est connecté, a la capacité de gérer les options et que le menu est situé dans l'emplacement "primary"
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) && $args->theme_location == 'primary' ) {
        
        // Si toutes les conditions sont remplies, on ajoute un lien vers l'interface d'administration de WordPress à la fin de la liste de menu
        $items .= '<li><a href="' . admin_url() . '">Admin</a></li>';
    }
    
    // La fonction doit retourner le contenu des éléments du menu, donc on retourne la variable $items
    return $items;
}

// On utilise le filtre "wp_nav_menu_items" pour appeler la fonction wpb_admin_menu
// Le filtre "wp_nav_menu_items" permet de modifier les éléments de menu générés par wp_nav_menu
// Les paramètres 10 est la priorité et 2 sont le nombre d'arguments que prend la fonction wpb_admin_menu
add_filter( 'wp_nav_menu_items', 'wpb_admin_menu', 10, 2 );
