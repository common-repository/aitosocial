<?php

namespace FSPoster\App\Pages\Dashboard\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;
?>

<script>
	fspConfig.comparison = {
		data: <?php echo json_encode( $fsp_params[ 'report3' ][ 'data' ] ); ?>,
		labels: <?php echo json_encode( $fsp_params[ 'report3' ][ 'labels' ] ); ?>
	};

	fspConfig.accComparison = {
		data: <?php echo json_encode( $fsp_params[ 'report4' ][ 'data' ] ); ?>,
		labels: <?php echo json_encode( $fsp_params[ 'report4' ][ 'labels' ] ); ?>,
        labels_full: <?php echo json_encode( $fsp_params[ 'report4' ][ 'labels_full' ] ); ?>
    };
</script>
<!-- modified plugin code -->
<div id="dashboardTooltip" class="fsp-dashboard-tooltip-container" style="display: none;width: 200px"></div>
<div class="fsp-row">
	<div class="fsp-col-12">
		<div class="fsp-dashboard-stats fsp-row">
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<span class="fsp-dashboard-stats-icon dashicons dashicons-share"></span>
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'sharesThisMonth' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Shares in this month' ); ?></span>
				</div>
			</div>
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<span class="fsp-dashboard-stats-icon dashicons dashicons-fullscreen-exit-alt"></span>
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'hitsThisMonth' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Clicks in this month' ); ?></span>
				</div>
			</div>
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<span class="fsp-dashboard-stats-icon dashicons dashicons-groups"></span>
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'accounts' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Total accounts' ); ?></span>
				</div>
			</div>
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<span class="fsp-dashboard-stats-icon dashicons dashicons-calendar-alt"></span>
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'hitsThisMonthSchedule' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Clicks from schedules' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="fsp-dashboard-graphs fsp-col-12 fsp-col-md-6">
		<div class="fsp-card">
			<div class="fsp-card-title">
				<?php echo fsp__( 'Shared posts count' ); ?>
				<select id="fspReports_clicksTypes2" class="fsp-select2-single">
					<option value="dayly"><?php echo fsp__( 'Daily' ); ?></option>
					<option value="monthly"><?php echo fsp__( 'Monthly' ); ?></option>
					<option value="yearly"><?php echo fsp__( 'Annually' ); ?></option>
				</select>
			</div>
			<div class="fsp-card-body fsp-p-20">
				<canvas id="fspReports_clicksChart2"></canvas>
			</div>
		</div>
	</div>
	<div class="fsp-dashboard-graphs fsp-col-12 fsp-col-md-6">
		<div class="fsp-card">
			<div class="fsp-card-title">
				<?php echo fsp__( 'Clicks count' ); ?>
				<select id="fspReports_clicksTypes" class="fsp-select2-single">
					<option value="dayly"><?php echo fsp__( 'Daily' ); ?></option>
					<option value="monthly"><?php echo fsp__( 'Monthly' ); ?></option>
					<option value="yearly"><?php echo fsp__( 'Annually' ); ?></option>
				</select>
			</div>
			<div class="fsp-card-body fsp-p-20">
				<canvas id="fspReports_clicksChart"></canvas>
			</div>
		</div>
	</div>
</div>