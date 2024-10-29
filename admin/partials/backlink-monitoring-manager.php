
<div id="wrap">
  <h1> <?php echo __( 'Backlink Monitoring Manager', $this->plugin_name ); ?></h1>
  <?php if ( isset( $_REQUEST['msg'] ) && 'success' ===  $_REQUEST['msg'] ) { ?>
    <div class="notice notice-success is-dismissible" id="blm-admin-notice">
        <p><?php echo __( 'Link Added', $this->plugin_name ); ?></p>
    </div>
  <?php } ?>

  <div class="card">
    <form id="add-valid-link" action="<?php echo admin_url('admin-post.php'); ?>" method="post">
    	<?php wp_nonce_field('backlink-monitoring-manager-add-link', 'backlink-monitoring-manager-add-link-nonce'); ?>
			<input type="hidden" name="action" value="backlink_monitoring_manager_add_link">
      <div id="test-div-id"></div>
      <img src="<?php echo admin_url( '/images/wpspin_light.gif' )?>" id="img_loading" style=" display: none;" />
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row">
              <label for="siteurl"><?php echo __( 'Link To', $this->plugin_name ); ?></label>
            </th>
            <td>
              <input name="tolink" type="url" id="tolink" class="regular-text code" required="true" value="" placeholder="<?php echo __( 'Please enter a valid URL.', $this->plugin_name ); ?>" />
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="home"> <?php echo __( 'Link From', 'backlink-monitoring-manager'); ?></label>
            </th>
            <td>
              <input name="fromlink" type="url" id="fromlink" aria-describedby="home-description" class="regular-text code" required="true" value="" 
              placeholder="<?php echo __( 'Please enter a valid URL.', $this->plugin_name ); ?>" />
            </td>
          </tr>
        </tbody>
      </table>
      <?php submit_button( __( 'Add Link' , 'textdomain'), 'button-primary add-link-btn', 'submit', true ); ?>
    </form>
        <?php $testListTable = new Back_Link_Monitoring_Manager_Child_WP_List_Table(); ?>
  </div>
  <div id="backlink-manager-core">
    <h2>
      <?php echo __( 'List of Available Links : ', $this->plugin_name ); ?>
    </h2>

    <?php
			$testListTable->prepare_items();
		?>
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $testListTable->display(); ?>
  </div>
</div>
