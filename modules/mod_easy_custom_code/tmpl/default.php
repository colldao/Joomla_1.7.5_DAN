<?php
/**
 * @copyright   Copyright (C) 2009-2013 ThemePartner. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/** @var $jsHelper JsModuleStaticContentHelper */
?>

<?php if(isset($jsHelper) && !$tpJsFile && $jsPlacement == 1): ?>
    <script type="text/javascript">
        <?php echo $customJs; ?>
    </script>
<?php endif; ?>
<?php if(!empty($customHtml)): ?>
<div class="tp-easy-custom-code<?php echo (!empty($moduleclass_sfx)) ? ' ' . $moduleclass_sfx : ''; ?>">
    <?php echo $customHtml; ?>
</div>
<?php endif; ?>

<?php if(isset($jsHelper) && !$tpJsFile && $jsPlacement == 2): ?>
    <script type="text/javascript">
        <?php echo $customJs; ?>
    </script>
<?php endif; ?>

<?php if(isset($jsHelper) && $jsHelper->isFileCreated() && $jsPlacement == 3): ?>
    <script type="text/javascript">
        (function() {
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = '<?php echo $jsHelper->getFullUrl(true); ?>';
            script.async = true;
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(script, s);
        })();
    </script>
<?php endif; ?>