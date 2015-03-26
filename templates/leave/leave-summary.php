<?php
$header_path = dirname(__FILE__) . '/header.php';
$header_path = apply_filters( 'hrm_header_path', $header_path, 'leave' );

if ( file_exists( $header_path ) ) {
    require_once $header_path;
}

?>
<div class="hrm-update-notification"></div>
<?php

$users = get_users( array( 'role' => 'hrm_employee' ));
$user_info = array( '' => '' );
foreach ( $users as $key => $user ) {
    $user_info[$user->ID] = $user->display_name;
}

$user_info = isset( $user_info ) ? $user_info : array();

$leave_types = Hrm_Settings::getInstance()->hrm_query('hrm_leave_type');

$leave_cat = array( '' => '' );
unset( $leave_types['total_row'] );
foreach ( $leave_types as $key => $leave_type ) {
    $leave_cat[$leave_type->id] = $leave_type->leave_type_name;
}

$leave_cat = isset( $leave_cat ) ? $leave_cat : array();


/*$search['leave_status'] = array(
    'type' => 'select',
    'option'   => hrm_Leave::getInstance()->leave_status(),
);*/

$search['type'] = array(
    'type'  => 'hidden',
    'value' => '_search'
);
$search['type_id'] = array(
    'label'    => __( 'Leave Type', 'hrm' ),
    'type'     => 'select',
    'required' => 'required',
    'class'  => 'hrm-chosen',
    'extra' => array(
        'data-hrm_validation' => true,
        'data-hrm_required' => true,
        'data-hrm_required_error_msg'=> __( 'This field is required', 'hrm' ),
    ),
    'option'   => $leave_cat,


);

$search['emp_id'] = array(
    'label'  => __( 'Employee Name', 'hrm' ),
    'required' => 'required',
    'extra' => array(
        'data-hrm_validation' => true,
        'data-hrm_required' => true,
        'data-hrm_required_error_msg'=> __( 'This field is required', 'hrm' ),
    ),
    'class'  => 'hrm-chosen',
    'type'   => 'select',
    'option' => $user_info,

);


$search['table_option'] = 'hrm_leave';
$search['action'] = 'hrm_search';
echo hrm_Settings::getInstance()->get_serarch_form( $search, 'Leave');
?>

<div id="hrm_Leave_list"></div>
<?php
$pagenum     = hrm_pagenum();
$limit       = hrm_result_limit();
if( isset( $_POST['type'] ) && ( $_POST['type'] == '_search' ) ) {
    $post         = $_POST;
    $search_satus = true;
    $results      = Hrm_Settings::getInstance()->search_query( $post, $limit, $pagenum );
    $total = $results['total_row'];
    unset( $results['total_row'] );
    $searc_leave_type = Hrm_Settings::getInstance()->edit_query( 'hrm_leave_type', $_POST['type_id'] );
} else {
    $results = array();
    $total = 0;
    $search_satus = false;
    $leave_type = array();
}



$work_in_week = get_option( 'hrm_work_week' );
$holidays = Hrm_Settings::getInstance()->hrm_query('hrm_holiday');
unset( $holidays['total_row'] );

$add_permission = hrm_user_can_access( $tab, $subtab, 'add' ) ? true : false;
$delete_permission = hrm_user_can_access( $tab, $subtab, 'delete' ) ? true : false;

$holiday_index = array();
foreach ( $holidays as $key => $holiday ) {
    $holiday_index = array_merge( $holiday_index, maybe_unserialize( $holiday->index_holiday ) );
}
$leave_action_acess = hrm_user_can_access( $tab, $subtab, 'leave_summary_action', true );
if ( $leave_action_acess === 'leave_summary_action' || $leave_action_acess ) {
    $action = true;
} else {
    $action = false;
}
$total_leave_count = 0;
foreach ( $results as $key => $value) {
    $leave_type = isset( $leave_cat[$value->leave_type_id] ) ? $leave_cat[$value->leave_type_id] : '';
    if ( $add_permission ) {
      $name_id = '<a href="#" class="hrm-editable" data-user_id='.$value->emp_id.' data-table_option="hrm_leave" data-id='.$value->id.'>'.$leave_type.'<a>';
    } else {
      $name_id = $leave_type;
    }

    if ( $delete_permission ) {
            $del_checkbox = '<input name="hrm_check['.$value->id.']" value="" type="checkbox">';
    } else {
            $del_checkbox = '';
    }
    if ( $action ) {
        $leave_action_dropdown = array(
            'class'    => 'hrm-leave-action',
            'extra'    => array(
                'data-leave_id' => $value->id,
            ),
            'option'   => hrm_Leave::getInstance()->leave_status(),
            'selected' => $value->leave_status
        );
        $leave_action = Hrm_Settings::getInstance()->select_field( 'leave_action', $leave_action_dropdown, $holiday_index );
    } else {
        $leave_action = __('Permission denied', 'hrm' );
    }

    $individual_leave_total = hrm_Leave::getInstance()->total_leave( $value->start_time, $value->end_time, $work_in_week, $holiday_index );
    $total_leave_count = $individual_leave_total + $total_leave_count;
    $body[] = array(
        $del_checkbox,
        $name_id,
        '<a href="#">'. $user_info[$value->emp_id] . '</a>',
        hrm_get_date2mysql( $value->start_time ),
        hrm_get_date2mysql( $value->end_time ),
        hrm_Leave::getInstance()->leave_status( $value->leave_status ),
        $value->leave_comments,
        $individual_leave_total,
        //hrm_Leave::getInstance()->leave_take( $value->start_time, $value->end_time, $work_in_week, $holiday_index ),
        //hrm_Leave::getInstance()->leave_remain( $value->start_time, $value->end_time, $work_in_week, $holiday_index ),
        $leave_action
    );

    $td_attr[] = array(
        'class="check-column"'
    );
}

if( isset( $_POST['type'] ) && ( $_POST['type'] == '_search' ) ) {
    $body[] = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '<strong>'. __( 'Total leave take', 'hrm' ) . '</strong>',
        '<strong>'. $total_leave_count .'</strong>'
    );
    $Balance = intval( $searc_leave_type['entitlement'] - $total_leave_count );
    $body[] = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '<strong>'. __( 'Leave Balance', 'hrm' ) . '</strong>',
        '<strong>'. $Balance .'</strong>'
    );
    $body[] = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '<strong>'. __( 'Leave Duration', 'hrm' ) . '</strong>',
        '<strong>'. hrm_get_date2mysql( $searc_leave_type['entitle_from'] ) . ' to ' . hrm_get_date2mysql( $searc_leave_type['entitle_to'] ) .'</strong>'

    );
    $leave_for = $searc_leave_type['leave_type_name'];
    $body[] = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '<strong>'. __( 'Total leave for ' . $leave_for, 'hrm' ) . '</strong>',
        '<strong>'. $searc_leave_type['entitlement'] .'</strong>'
    );

}

$table = array();
$del_checkbox = ( $delete_permission ) ? '<input type="checkbox">' : '';
$table['head'] = array(
    $del_checkbox,
    __('Leave Type', 'erhm' ),
    __('Employee Name', 'erhm' ),
    __('Start Date', 'erhm' ),
    __('End Date', 'erhm' ),
    __('Leave Status', 'erhm' ),
    __('Comments', 'erhm' ),
    __('Leave (day, include holiday and leave week) ', 'erhm' ),
   // __('Leave Take(day)', 'erhm' ),
   // __('Leave Remain(day)', 'erhm' ),
    __('Action', 'hrm' )
);
$table['body']       = isset( $body ) ? $body : '';
$table['td_attr']    = isset( $td_attr ) ? $td_attr : '';
$table['th_attr']    = array( 'class="check-column"' );
$table['table_attr'] = array( 'class' => 'widefat' );
$table['table']      = 'hrm_leave';
$table['action']     = 'hrm_delete';
$table['tab']        = $tab;
$table['subtab']     = $subtab;

echo Hrm_Settings::getInstance()->table( $table );
//table
echo hrm_Settings::getInstance()->pagination( $total, $limit, $pagenum );
$url = hrm_Settings::getInstance()->get_current_page_url( $page, $tab, $subtab );
$file_path = urlencode(__FILE__);
?>

<script type="text/javascript">
jQuery(function($) {
    hrm_dataAttr = {
       add_form_generator_action : 'add_form',
       add_form_apppend_wrap : 'hrm_Leave_list',
       class_name : 'hrm_Leave',
       function_name : 'assign',
       redirect : '<?php echo $url; ?>',
       user_info: '<?php echo json_encode( $user_info ); ?>',
       leave_cat: '<?php echo json_encode( $leave_cat ); ?>',
       page: '<?php echo $page; ?>',
       tab: '<?php echo $tab; ?>',
       subtab: '<?php echo $subtab; ?>',
       req_frm: '<?php echo $file_path; ?>',
       limit: '<?php echo $limit; ?>',
       search_satus: '<?php echo $search_satus; ?>',
    };
});
</script>