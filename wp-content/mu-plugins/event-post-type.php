<?php
    function university_post_types(){
        //event post type
        register_post_type("event", array(
            'show_in_rest' => true,
            'capability_type' => 'event',
            'map_meta_cap' => true,
            'supports' => array('title','editor','excerpt','custom-fields','thumbnail'),
            'rewrite' => array('slug' =>'events'),
		    'has_archive' => true,
            'public' => true,
            'labels' => array(
                'name' => 'Events',
                "add_new_item" => 'Add New Element',
                'edit_item' => 'Edit Event',
                'all_items' => 'All Events',
                'singular_name' => 'Event'
            ),
            'menu_icon' => 'dashicons-calendar'
        ));
        //program post type
       register_post_type("program",array(
            'show_in_rest' => true,
            'supports' => array('title'),
            'rewrite' => array('slug'=>'programs'),
            'has_archive' => true,
            'public' => true,
            'labels' => array(
                'name'=>'Programs',
                "add_new_item" => 'Add New Program',
                'edit_item' => 'Edit Program',
                'all_items' => 'All Programs',
                'singular_name'=>'Program'
            ),
            'menu_icon' => 'dashicons-awards'
        )); 
         //professor post type
       register_post_type("professor",array(
        'show_in_rest' => true,
        'supports' => array('title','editor','thumbnail'),
        'rewrite' => array('slug'=>'professors'),
        'has_archive' => true,
        'public' => true,
        'labels' => array(
            'name'=>'Professors',
            "add_new_item" => 'Add New Professor',
            'edit_item' => 'Edit Professor',
            'all_items' => 'All Professors',
            'singular_name'=>'Professor'
        ),
        'menu_icon' => 'dashicons-welcome-learn-more  '
    )); 
       //campus post type
       register_post_type("campus",array(
        'show_in_rest' => true,
        'capability_type' => 'campus',
        'map_meta_cap' => true,
        'supports' => array('title','editor','thumbnail'),
        'rewrite' => array('slug'=>'campuses'),
        'has_archive' => true,
        'public' => true,
        'labels' => array(
            'name'=>'Campuses',
            "add_new_item" => 'Add New Campus',
            'edit_item' => 'Edit Campus',
            'all_items' => 'All Campuses',
            'singular_name'=>'Campus'
        ),
        'menu_icon' => 'dashicons-location-alt  '
    )); 
        //note post type
       register_post_type("note",array(
        'show_in_rest' => true,
        'capability_type' => 'note',
        'map_meta_cap' => true,
        'supports' => array('title','editor'),
        'rewrite' => array('slug'=>'notes'),
        'has_archive' => true,
        'public' => false,
        'show_ui' => true,
        'labels' => array(
            'name'=>'Notes',
            "add_new_item" => 'Add New Notes',
            'edit_item' => 'Edit Notes',
            'all_items' => 'All Notes',
            'singular_name'=>'Notes'
        ),
        'menu_icon' => 'dashicons-welcome-write-blog  '
    )); 
        //Like post type
       register_post_type("like",array(
        'supports' => array('title'),
        'rewrite' => array('slug'=>'likes'),
        'public' => false,
        'show_ui' => true,
        'labels' => array(
            'name'=>'Likes',
            "add_new_item" => 'Add New Likes',
            'edit_item' => 'Edit Likes',
            'all_items' => 'All Likes',
            'singular_name'=>'Likes'
        ),
        'menu_icon' => 'dashicons-heart  '
    )); 
    }
    
    add_action('init','university_post_types');