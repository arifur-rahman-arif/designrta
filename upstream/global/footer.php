<?php
if ( ! defined('ABSPATH')) {
    exit;
}

$pageTitle = get_bloginfo('name');

$footerText = sprintf('&copy; %s %s', $pageTitle, date('Y'));
$footerText = apply_filters('upstream_footer_text', $footerText);
?>
<footer>
   
</footer>
</div>
</div>

<?php wp_footer(); ?>

</body>
</html>
