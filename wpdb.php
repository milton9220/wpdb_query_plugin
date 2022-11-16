<?php
/*
Plugin Name: WPDB Plugin
Plugin URI:
Description:
Version: 1.0.0
Author: Milton
Author URI:
License: GPLv2 or later
Text Domain: wpdb-demo
 */

function wpdbdemo_init() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(250),
			email VARCHAR(250),
            age INT,
			PRIMARY KEY (id)
	);";
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta( $sql );
}

register_activation_hook( __FILE__, "wpdbdemo_init" );

add_action( 'admin_enqueue_scripts', function ( $hook ) {

    if ( 'toplevel_page_wpdb-demo' == $hook ) {
        wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
        wp_enqueue_style( 'wpdb-demo-css', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );
        wp_enqueue_script( 'wpdb-demo-js', plugin_dir_url( __FILE__ ) . "assets/js/app.js", array( 'jquery' ), time(), true );
        $nonce = wp_create_nonce( 'display_result' );
        wp_localize_script(
            'wpdb-demo-js',
            'plugindata',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => $nonce )
        );
    }

} );

add_action( 'wp_ajax_display_result', function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';

    if ( wp_verify_nonce( $_POST['nonce'], 'display_result' ) ) {
        $task = $_POST['task'];

        if ( 'add-new-record' == $task ) {
            $wpdb->insert( $table_name, [
                'name'  => 'Milton',
                'email' => 'milton123@gmail.com',
                'age'   => 23,
            ] );
            echo "Added Successfully<br/>";
            echo "ID:{$wpdb->insert_id} <br/>";
        } elseif ( 'replace-or-insert' == $task ) {
            $wpdb->replace( $table_name, [
                'id'    => 7,
                'name'  => 'Malu',
                'email' => 'malu@gmail.com',
                'age'   => 23,
            ] );
            echo "Operation Done<br/>";
            echo "ID:{$wpdb->insert_id} <br/>";
        } elseif ( 'update-data' == $task ) {
            $person = array( 'age' => 27 );
            $result = $wpdb->update( $table_name, $person, ['id' => 1] );
            echo "Operation Done.Result:{$result}";
        } elseif ( 'load-single-row' == $task ) {
            $data = $wpdb->get_row( "select * from {$table_name} where id=1" );
            print_r( $data );

            $data = $wpdb->get_row( "select * from {$table_name} where id=1", ARRAY_A );
            print_r( $data );

            $data = $wpdb->get_row( "select * from {$table_name} where id=1", ARRAY_N );
            print_r( $data );
        } elseif ( 'load-multiple-row' == $task ) {
            $data = $wpdb->get_results( "select * from {$table_name}" );
            print_r( $data );

            $data = $wpdb->get_results( "select * from {$table_name} where id>2" );
            print_r( $data );

            $data = $wpdb->get_results( "select email,name,age from {$table_name}", OBJECT_K );
            print_r( $data );
        } elseif ( 'add-multiple' == $task ) {
            $persons = array(
                array(
                    'name'  => 'Karim',
                    'email' => 'karim123@gmail.com',
                    'age'   => 30,
                ),
                array(
                    'name'  => 'Boltu',
                    'email' => 'boltu123@gmail.com',
                    'age'   => 35,
                ),
            );
            foreach($persons as $person){
               $wpdb->insert($table_name,$person);
            }
            echo "Operation Done<br/>";
        }
        elseif('prepared-statement'==$task){
            $id=2;
            $age=25;
            // $prepared_statement=$wpdb->prepare("select * from {$table_name} where id=%d",$id);
            $prepared_statement=$wpdb->prepare("select * from {$table_name} where id > %d and age > %s",$id,$age);
            $data=$wpdb->get_results($prepared_statement,ARRAY_A);
            print_r($data);
        }
        elseif('single-column'==$task){
            $query="SELECT email FROM {$table_name}";
            $result=$wpdb->get_col($query);
            print_r($result);
        }
        elseif('delete-data'==$task){
            $result=$wpdb->delete($table_name,['id'=>6]);
            echo "Delete Result:".$result;
        }

    }

    die( 0 );

} );

add_action( 'admin_menu', function () {
    add_menu_page( 'WPDB Plugin', 'WPDB Plugin', 'manage_options', 'wpdb-demo', 'wpdbdemo_admin_page' );
} );

function wpdbdemo_admin_page() {
    ?>
        <div class="container" style="padding-top:20px;">
            <h1>WPDB Plugin</h1>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='add-new-record'>Add New Data</button>
                        <button class="action-button" data-task='replace-or-insert'>Replace or Insert</button>
                        <button class="action-button" data-task='update-data'>Update Data</button>
                        <button class="action-button" data-task='load-single-row'>Load Single Row</button>
                        <button class="action-button" data-task='load-multiple-row'>Load Multiple Row</button>
                        <button class="action-button" data-task='add-multiple'>Add Multiple Row</button>
                        <button class="action-button" data-task='prepared-statement'>Prepared Statement</button>
                        <button class="action-button" data-task='single-column'>Display Single Column</button>
                        <button class="action-button" data-task='single-var'>Display Variable</button>
                        <button class="action-button" data-task='delete-data'>Delete Data</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title">Result</h3>
                        <div id="plugin-demo-result" class="plugin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
