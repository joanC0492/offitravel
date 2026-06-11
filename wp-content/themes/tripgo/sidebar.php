<?php
$sidebar = apply_filters( 'tripgo_theme_sidebar', '' );
if ($sidebar == 'layout_1c' || $sidebar == ''){
    return;
}
?>

<?php if(is_active_sidebar('main-sidebar')){ ?>
        <aside id="sidebar" class="sidebar">
            <?php  dynamic_sidebar('main-sidebar'); ?>
        </aside>
<?php } ?>