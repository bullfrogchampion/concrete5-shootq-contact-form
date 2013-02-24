<?php 
defined('C5_EXECUTE') or die("Access Denied.");
?>

<div class="ccm-block-field-group">
	<h2>Thank You Message</h2>
	<textarea id="thanksMsg" name="thanksMsg" rows="5" style="width: 95%;"><?php echo $thanksMsg; ?></textarea>
</div>

<div class="ccm-block-field-group">
	<h2>Send Notification Emails To</h2>
	<input type="text" id="notifyEmail" name="notifyEmail" style="width: 95%;" value="<?php echo $notifyEmail; ?>" />
	<i>Only used if inserting into ShootQ fails. Separate multiple email addresses with commas</i>
</div>

<div class="ccm-block-field-group">
    <h2>ShootQ API Key</h2>
    <input type="text" id="apiKey" name="apiKey" style="width: 95%;" value="<?php echo $apiKey; ?>" />
</div>

<div class="ccm-block-field-group">
    <h2>ShootQ Brand Abbreviation</h2>
    <input type="text" id="brandAbbreviation" name="brandAbbreviation" style="width: 95%;" value="<?php echo $brandAbbreviation; ?>" />
</div>

<div class="ccm-block-field-group">
    <h2>ShootQ Event Type</h2>
    <input type="text" id="eventType" name="eventType" style="width: 95%;" value="<?php echo $eventType; ?>" />
</div>

