<div class="pa-addons-tutorial">
    <div class="pa-addons-tutorial-inner">
        <div class="pa-addons-tutorial__title">
            <h3>Welcome to PeepSo!</h3>
        </div>
        <div class="pa-addons-tutorial__desc">
            <p>
                <i class="gci gci-info-circle"></i>
                The PeepSo Installer lets you easily install the paid PeepSo add-ons (themes and plugins).
            </p>

            <p>
                <i class="gci gci-key"></i>
                To begin, <b><a href="https://www.peepso.com/profile/?*/edd/licenses/" taget="_blank">copy your license key</a></b> into the <b>field below</b> and click the <b class="ps-emphasis">Check</b> button.
            </p>

            <p>
                <i class="gci gci-gift"></i> If you don't have a paid PeepSo License yet, <b><a href="<?php echo admin_url('admin.php?page=peepso-installer&action=peepso-free');?>">get the PeepSo Free Bundle</a></b> - it comes with <b>a few free plugins and the Gecko Theme</b>.
            </p>
            <p>&nbsp;</p>

            <p><i class="gci gci-arrow-alt-circle-down"></i>  After your license is verified, you will be able to install and activate any add-ons included in your plan.</p>

            <p><i class="gci gci-list-alt"></i> To install & activate <b>multiple add-ons</b>, click <b class="ps-emphasis">Show bulk actions</b>, select multiple add-ons and use the <b class="ps-emphasis">Install</b> or <b class="ps-emphasis">Activate</b> buttons.</p>

            <hr>

            <?php PeepSoTemplate::exec_template('admin','admin_notice_help');?>

        </div>
        <a class="pa-addons-tutorial__close" href="#">
            <i class="gcir gci-times-circle"></i>
        </a>
    </div>
</div>
