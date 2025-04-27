<?php
/*
Template Name: Dashboard Template
*/
?>

<div class="container">
    <?php
    while (have_posts()) : the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php ?>