<div class="hrm-update-notification"></div>
<?php

if ( hrm_current_user_role() == 'hrm_employee' ) {
    $employer_id = get_current_user_id();
} else {
    $employer_id = isset( $_REQUEST['employee_id'] ) ? trim( $_REQUEST['employee_id'] ) : '';
}

$leave_types = Hrm_Settings::getInstance()->hrm_query('hrm_leave_type');

$leave_cat = array( '' => '' );
unset( $leave_types['total_row'] );
foreach ( $leave_types as $key => $leave_type ) {
    $leave_cat[$leave_type->id] = $leave_type->leave_type_name;
}

$leave_cat = isset( $leave_cat ) ? $leave_cat : array();
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
    'type' => 'hidden',
    'value' => $employer_id
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

$leave_types = Hrm_Settings::getInstance()->hrm_query('hrm_leave_type');
unset( $leave_types['total_row'] );
foreach ( $leave_types as $key => $leave_type ) {
    $leave_cat[$leave_type->id] = $leave_type->leave_type_name;
}

$users = get_users();
foreach ( $users as $key => $user ) {
    $user_info[$user->ID] = $user->display_name;
}

$work_in_week = get_option( 'hrm_work_week' );
$holidays = Hrm_Settings::getInstance()->hrm_query('hrm_holiday');
unset( $holidays['total_row'] );
$holiday_index = array();

foreach ( $holidays as $key => $holiday ) {
    $holiday_index = array_merge( $holiday_index, maybe_unserialize( $holiday->index_holiday ) );
}

$total_leave_count = 0;
$body = array();

foreach ( $results as $key => $value) {
    $leave_type = isset( $leave_cat[$value->leave_type_id] ) ? $leave_cat[$value->leave_type_id] : '';

    $name_id = $leave_type;

    $del_checkbox = '';

    $individual_leave_total = hrm_Leave::getInstance()->total_leave( $value->start_time, $value->end_time, $work_in_week, $holiday_index );
    $total_leave_count = $individual_leave_total + $total_leave_count;
    $body[] = array(
        $name_id,
        '<a href="#">'. $user_info[$value->emp_id] . '</a>',
        hrm_get_date2mysql( $value->start_time ),
        hrm_get_date2mysql( $value->end_time ),
        hrm_Leave::getInstance()->leave_status( $value->leave_status ),
        $value->leave_comments,
        $individual_leave_total,
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
        '<strong>'. __( 'Leave Balance', 'hrm' ) . '</strong>',
        '<strong>'. $Balance .'</strong>'
    );
    $body[] = array(
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

$table['head'] = array(
    __('Leave Type', 'erhm' ),
    __('Employee Name', 'erhm' ),
    __('Start Date', 'erhm' ),
    __('End Date', 'erhm' ),
    __('Leave Status', 'erhm' ),
    __('Comments', 'erhm' ),
    __('Leave (day, include holiday and leave week) ', 'erhm' ),
);

$table['body']       = isset( $body ) ? $body : array();
$table['td_attr']    = isset( $td_attr ) ? $td_attr : array();
$table['th_attr']    = array( 'class="check-column"' );
$table['table_attr'] = array( 'class' => 'widefat' );
$table['table']      = 'hrm_leave';
$table['action']     = 'hrm_delete';
$table['tab']        = $tab;
$table['subtab']     = $subtab;
$table['delete_button'] = false;

echo Hrm_Settings::getInstance()->table( $table );
//table

$leave_cat = isset( $leave_cat ) && is_array( $leave_cat ) ? $leave_cat : array();
$url = Hrm_Settings::getInstance()->get_current_page_url( $page, $tab, $subtab ) . '&employee_id='. $employer_id;
$file_path = urlencode(__FILE__);
?>
<script type="text/javascript">
jQuery(function($) {
    hrm_dataAttr = {
       add_form_generator_action : 'add_form',
       add_form_apppend_wrap : 'hrm_Leave_list',
       class_name : 'Hrm_Leave',
       redirect : '<?php echo $url; ?>',
       function_name : 'individula_apply_leave',
       user_id: '<?php echo $employer_id; ?>',
       user_info: '<?php echo json_encode( $user_info ); ?>',
       leave_cat: '<?php echo json_encode( $leave_cat ); ?>',
       employee_id: "<?php echo $employer_id; ?>",
       page: '<?php echo $page; ?>',
       tab: '<?php echo $tab; ?>',
       subtab: '<?php echo $subtab; ?>',
       req_frm: '<?php echo $file_path; ?>',
       search_satus: '<?php echo $search_satus; ?>',
    };
});
</script>