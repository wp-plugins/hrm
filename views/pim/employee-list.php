<?php
//search form

$search['user'] = array(
    'label' => __( 'User Name/E-mail', 'hrm' ),
    'type' => 'text',
    'desc' => 'please insert username or E-mail',
);
$search['first_name'] = array(
    'label' => __( 'First Name', 'hrm' ),
    'type' => 'text',
    'desc' => 'please insert First Name',
);

$search['last_name'] = array(
    'label' => __( 'Last Name', 'hrm' ),
    'type' => 'text',
    'desc' => 'please insert Last Name',
);

$search['status'] = array(
    'label' => __( 'Status', 'hrm' ),
    'type' => 'select',
    'option' => array(
        '' => __('--Select--', 'hrm'),
        'yes' => __('Enable', 'hrm' ),
        'no' => __('Disable', 'hrm' )
    ),
);

$search['mobile'] = array(
    'label' => __( 'Mobile', 'hrm' ),
    'type' => 'text',
    'desc' => 'please insert mobile number',
);

$search['action'] = 'hrm_search';
$search['table_option'] = 'hrm_employee';
echo hrm_Settings::getInstance()->get_serarch_form( $search, 'Employee Information');
?>
<div id="hrm-eployee-list"></div>
<?php


    $limit = isset( $_GET['pagination'] ) ? $_GET['pagination'] : 10;
    if( isset( $_GET['type'] ) && ( $_GET['type'] == '_search' ) ) {
        $employers = hrm_Employeelist::getInstance()->employeer_search_query( $_GET, $limit );

    } else {
        $employers = hrm_Employeelist::getInstance()->get_employee( $limit );
    }

    if ( !$employers ) {
        return;
    }
    $total = $employers->total_users;
    $employers = $employers->results;


$add_permission = hrm_user_can_access( $tab, null, 'add' ) ? true : false;
$delete_permission = hrm_user_can_access( $tab, null, 'delete' ) ? true : false;

    foreach ( $employers as $key => $employer ) {

        if ( $delete_permission ) {
            $del_checkbox = '<input name="hrm_check['.$employer->ID.']" value="'.$employer->ID.'" type="checkbox">';
        } else {
            $del_checkbox = '';
        }

        if ( $add_permission ) {
            $name_id = '<a href="#" class="hrm-editable" data-action="employer_edit" data-table_option="" data-id='.$employer->ID.'>'.get_user_meta( $employer->ID, 'first_name', true ).'<a>';
        } else {
            $name_id = get_user_meta( $employer->ID, 'first_name', true );
        }
        $status = ( get_user_meta( $employer->ID, '_status', true ) == 'yes' ) ? 'Enable' : 'Disable';
        $admin_url = add_query_arg(
            array(
                'page'        => 'hrm_pim',
                'tab'         => 'personal',
                'subtab'      => 'personal_info',
                'employee_id' => $employer->ID
            ), admin_url( 'admin.php' )
        );

        $body[] = array(
            $del_checkbox,
            '<a href="'.$admin_url.'">'.$employer->display_name.'</a>',
            $name_id,
            get_user_meta( $employer->ID, 'last_name', true ),
            $status,
            get_user_meta( $employer->ID, '_mob_number', true ),
            get_date2mysql( get_user_meta( $employer->ID, '_joined_date', true ) ),

        );

        $td_attr[] = array(
            'class="check-column"'
        );
    }

    $del_checkbox = ( $delete_permission ) ? '<input type="checkbox">' : '';
    $table['head'] = array( $del_checkbox,'Profile', 'First Name', 'Last Name', 'Status', 'Mobile', 'Joined Date' );
    $table['body'] = isset( $body ) ? $body : '';


    $table['td_attr'] = isset( $td_attr ) ?$td_attr : '';
    $table['th_attr'] = array( 'class="check-column"' );
    $table['table_attr'] = array( 'class' => 'widefat' );

    $table['table'] = 'hrm_employee';
    $table['tab'] = $tab;
    $table['action'] = 'employee_delete';
    $table['table_attr'] = array( 'class' => 'widefat' );

    echo hrm_Settings::getInstance()->table( $table );
    //table

    //pagination
    echo hrm_Settings::getInstance()->pagination( $total, $limit );

?>
<?php $url = hrm_Settings::getInstance()->get_current_page_url( $page, $tab, $subtab ); ?>

<?php
$job_titles = hrm_Settings::getInstance()->hrm_query( 'hrm_job_title' );
unset($job_titles['total_row']);
foreach ($job_titles as $key => $value) {
    $job_title[$value->id] = $value->job_title;
}
$job_title = isset( $job_title ) ? $job_title : array();
$job_categorys = hrm_Settings::getInstance()->hrm_query( 'hrm_job_category' );

unset($job_categorys['total_row']);
foreach ($job_categorys as $key => $value) {
    $job_category[$value->id] = $value->name;
}
$job_category = isset( $job_category ) ? $job_category : array();
$locations = hrm_Settings::getInstance()->hrm_query( 'hrm_location' );
unset($locations['total_row']);
foreach ($locations as $key => $value) {
    $location[$value->id] = $value->name;
}
$location = isset( $location ) ? $location : array();
?>
<script type="text/javascript">
jQuery(function($) {
    hrm_dataAttr = {
        add_form_generator_action : 'add_form',
        add_form_apppend_wrap : 'hrm-eployee-list',
        redirect : '<?php echo $url; ?>',
        class_name : 'hrm_Employeelist',
        function_name : 'new_employee_form',
        job_title : '<?php echo json_encode( $job_title ); ?>',
        job_category : '<?php echo json_encode( $job_category ); ?>',
        location : '<?php echo json_encode( $location ); ?>',
    };
});
</script>