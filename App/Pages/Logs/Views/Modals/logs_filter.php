<?php
use FSPoster\App\Providers\Helper;
defined( 'MODAL' ) or exit;
?>
<div class="fsp-modal-header">
    <div class="fsp-modal-title">
        <div class="fsp-modal-title-icon">
            <i class="fas fa-filter"></i>
        </div>
        <div class="fsp-modal-title-text">
            <?php echo fsp__( 'Filter logs' ); ?>
        </div>
    </div>
    <div class="fsp-logs-filter-modal fsp-modal-close" data-modal-close="true">
        <i class="fas fa-times"></i>
    </div>
</div>
<div class="fsp-modal-body">
    <div class="fsp-form-group <?php echo current_user_can('administrator') ? '' : 'fsp-hide'; ?>">
        <label><?php echo fsp__( 'Filter users' ); ?></label>
        <select id="fspModalShowLogsOf" class="fsp-form-select">
            <option value="all" <?php echo( Helper::getOption('show_logs_of', 'own') === 'all' ? 'selected' : '' ); ?>><?php echo fsp__( 'All logs' ); ?></option>
            <option value="own" <?php echo( Helper::getOption('show_logs_of', 'own') === 'own' ? 'selected' : '' ); ?>><?php echo fsp__( 'My logs' ); ?></option>
        </select>
    </div>
    <div class="fsp-form-group">
        <label><?php echo fsp__( 'Filter results' ); ?></label>
        <select id="fspModalFilterSelector" class="fsp-form-select">
            <option value="all" <?php echo( $fsp_params['filter_results'] === 'all' ? 'selected' : '' ); ?>><?php echo fsp__( 'All' ); ?></option>
            <option value="ok" <?php echo( $fsp_params['filter_results'] === 'ok' ? 'selected' : '' ); ?>><?php echo fsp__( 'Success' ); ?></option>
            <option value="error" <?php echo( $fsp_params['filter_results'] === 'error' ? 'selected' : '' ); ?>><?php echo fsp__( 'Error' ); ?></option>
        </select>
    </div>
    <div class="fsp-form-group">
        <label><?php echo fsp__( 'Filter social networks' ); ?></label>
        <select id="fspModalSnSelector" class="fsp-form-select">
            <option value="all" <?php echo $fsp_params['sn_filter'] === 'all' ? 'selected' : ''; ?>><?php echo fsp__('All') ?></option>
            <option value="fb" <?php echo $fsp_params['sn_filter'] === 'fb' ? 'selected' : ''; ?>><?php echo fsp__('Facebook') ?></option>
            <option value="instagram" <?php echo $fsp_params['sn_filter'] === 'instagram' ? 'selected' : ''; ?>><?php echo fsp__('Instagram') ?></option>
            <option value="threads" <?php echo $fsp_params['sn_filter'] === 'threads' ? 'selected' : ''; ?>><?php echo fsp__('Threads') ?></option>
            <option value="twitter" <?php echo $fsp_params['sn_filter'] === 'twitter' ? 'selected' : ''; ?>><?php echo fsp__('Twitter') ?></option>
            <option value="planly" <?php echo $fsp_params['sn_filter'] === 'planly' ? 'selected' : ''; ?>><?php echo fsp__('Planly') ?></option>
            <option value="linkedin" <?php echo $fsp_params['sn_filter'] === 'linkedin' ? 'selected' : ''; ?>><?php echo fsp__('Linkedin') ?></option>
            <option value="pinterest" <?php echo $fsp_params['sn_filter'] === 'pinterest' ? 'selected' : ''; ?>><?php echo fsp__('Pinterest') ?></option>
            <option value="webhook" <?php echo $fsp_params['sn_filter'] === 'webhook' ? 'selected' : ''; ?>><?php echo fsp__('Webhook') ?></option>
        </select>
    </div>
    <div class="fsp-form-group">
        <label><?php echo fsp__( 'Count of rows' ); ?></label>
        <select id="fspModalRowsSelector" class="fsp-form-select">
            <option <?php echo Helper::getOption( 'logs_rows_count_' . get_current_user_id(), '4' ) === '4' ? 'selected' : ''; ?>>4</option>
            <option <?php echo Helper::getOption( 'logs_rows_count_' . get_current_user_id(), '4' ) === '8' ? 'selected' : ''; ?>>8</option>
            <option <?php echo Helper::getOption( 'logs_rows_count_' . get_current_user_id(), '4' ) === '15' ? 'selected' : ''; ?>>15</option>
        </select>
    </div>
</div>
<div class="fsp-modal-footer">
    <button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
    <button id="fspModalFilterLogsBtn" class="fsp-button"><?php echo fsp__( 'APPLY FILTERS' ); ?></button>
</div>