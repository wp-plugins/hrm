<div class="hrm-update-notification"></div>
<?php
$hidden_form['punch_without_frm'] = array(
    'label'      => __( 'Punch form disable' ),
    'type'       => 'checkbox',
    'desc'       => 'Punch in/out without submit form',
    'wrap_class' => 'hrm-parent-field',
    'fields'     => array(
        array(
            'label' => __( 'Form Disable', 'hrm' ),
            'value' => 'yes',
            'checked' => get_option( 'hrm_punch_form_status', true ),
            'class'  => 'hrm-punch-form-status'
        )
    )
);
$hidden_form['header'] = __( 'Punch Form Enable', 'hrm' );
//echo Hrm_Settings::checkbox_field( 'punch_without_frm', $hidden_form );
echo hrm_Settings::getInstance()->form_field_only( $hidden_form  );
?>
<div id="hrm-admin-role"></div>
<?php
$jk = get_option( 'pro_test_role' );

//hidden form

global $wp_roles;

if ( !$wp_roles ) {
    $wp_roles = new WP_Roles();
}
//echo '<pre>'; print_r( $wp_roles ); echo '</pre>'; die();
$role_names = $wp_roles->get_names();
$wp_built_in_role = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber', 'hrm_employee' );

$add_permission = hrm_user_can_access( $tab, $subtab, 'add' ) ? true : false;
$delete_permission = hrm_user_can_access( $tab, $subtab, 'delete' ) ? true : false;
$current_user_role = hrm_current_user_role();

foreach ( $role_names as $name => $display_name) {
    if ( $current_user_role == $name ) {
        continue;
    }
    if ( $add_permission ) {
        $name_id = '<a data-role_name="'.$name.'" data-action="get_role" data-display_name="'.$display_name.'" class="hrm-editable" href="#">'.$name.'</a>';
    } else {
        $name_id = $name;
    }
    $body[] = array(
        $name_id,
        $display_name
    );
}

$table['head']          = array( 'User Role', 'Display Name' );
$table['body']          = isset( $body ) ? $body : array();


$table['td_attr']       = isset( $td_attr ) ? $td_attr : '';
$table['th_attr']       = array( 'class="check-column"' );
$table['table_attr']    = array( 'class' => 'widefat' );

$table['action']        = 'role_delete';
$table['table_attr']    = array( 'class' => 'widefat' );
$table['tab']           = $tab;
$table['subtab']        = $subtab;
$table['add_button']    = false;
$table['delete_button'] = false;

echo Hrm_Settings::getInstance()->table( $table );
$file_path = urlencode(__FILE__);
$url = Hrm_Settings::getInstance()->get_current_page_url( $page, $tab, $subtab ); ?>

<script type="text/javascript">
    jQuery(function($) {
        hrm_dataAttr = {
           add_form_generator_action : 'add_form',
           add_form_apppend_wrap : 'hrm-admin-role',
           class_name : 'Hrm_Time',
           redirect : '<?php echo $url; ?>',
           function_name : 'role_permission',
           tab: '<?php echo $tab; ?>',
           page: '<?php echo $page; ?>',
           tab: '<?php echo $tab; ?>',
           subtab: '<?php echo $subtab; ?>',
           req_frm: '<?php echo $file_path; ?>',
        };
    });
</script>