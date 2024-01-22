<?php
if( empty($props) )
    $props = array(); // Empty array so it will not loop

//Modified content
$table_content = '<table id="requests-table">';
$table_content .= '<thead><tr><th>' . __('Shows', 'powerpress_network') . '</th><th> ' . __('RSS', 'powerpress_network') . '</th><th>' . __('Approval', 'powerpress_network') . '</th></tr></thead>';
$table_content .= '<tbody>';
if (!isset($props['danger'])) {
    for ($i = 0; $i < count($props); ++$i) {
        $class = 'odd-row';
        if ($i % 2 == 0) {
            $class = 'even-row';
        }
        $table_content .= '<tr class="' . $class . '">';
        $table_content .= '<td style="padding: 1.5em 1em;" class="' . $class . '"><span style="font-weight: bold;">' . esc_html($props[$i]['program_title']) . '</span></td>';
        $table_content .= '<td class="' . $class . '"><a target="_blank" style="margin: 0;" href="' . $props[$i]['program_rssurl'] . '">' . __('View RSS', 'powerpress_network') . '</a></td>';
        switch ($props[$i]['app_status']) {
            case 0:
                $table_content .= '<td class="' . $class . '">
            <select onchange="approveProgram(' . esc_js($props[$i]['applicant_id']) . ', this.value)" class="application-dropdown">
                                    <option value="Pending" selected >Pending</option>
                                    <option value="1"><button class="applicantButton" style="background-color: #007800; display: block; " onclick="approveProgram(' . esc_js($props[$i]['applicant_id']) . ', true)">' . __('Approve', 'powerpress') . '</button></option>
                                    <option value="0"><button class="applicantButton" style="background-color: #B00000; display: block;" onclick="approveProgram(' . esc_js($props[$i]['applicant_id']) . ', false)">' . __('Reject', 'powerpress') . '</button></option>
            </select>
                                </td>';
                break;
            case 1:
                $table_content .= '<td class="' . $class . '">
                                    <div class="applicantButton" style="background-color: #007800; display: inline-block; ">' . __('Approved', 'powerpress') . '</div>&nbsp;
                            
                                    <a href="" onclick="approveProgram(' . esc_js($props[$i]['applicant_id']) . ', false, true); return false;">' . __('Undo', 'powerpress') . '</a>
                            </td>';
                break;
            case -1:
                $table_content .= '<td class="' . $class . '">
                                    <div class="applicantButton" style="background-color: #B00000; display: inline-block;">' . __('Rejected', 'powerpress') . '</div>&nbsp;
                     
                                    <a title="Undo" href="" onclick="approveProgram(' . esc_js($props[$i]['applicant_id']) . ', false, true); return false;">' . __('Undo', 'powerpress') . '</a> &nbsp;&nbsp;
                                    <a title="Delete" href="" onclick="approveProgram(' . esc_js($props[$i]['applicant_id']) . ', false, false, true); return false;">' . __('Delete', 'powerpress') . '</a>
                            </td>';
                break;
        }
        $table_content .= '</tr>';
    }
}
$table_content .= '</tbody>';
$table_content.='</table>';

//End modified content


?>
<h2 style="font-weight: bold; font-size: 150%; margin-bottom: 1ch;"><?php echo esc_html(get_option('powerpress_network_title') ) . ' Requests'; ?>
    <form method="post" id="applicationForm" class="top-right-corner">
        <button class="cacheButton" onclick="createApplicationPage('Application','applicationForm', 'Application Page')">
            <?php echo esc_html(__('CREATE APPLICATION', 'powerpress-network'));?>
        </button>
    </form>
</h2>
<h4 style="margin: 0; font-size: 120%;"><?php echo esc_html(__('Review shows who have applied to join your network. External shows can be added here.', 'powerpress-network'));?></h4><br>

<?php echo $table_content; ?>
<form id='applicationCreateForm' method="POST">
</form>

